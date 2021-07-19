<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\Products;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Models\CategoryTenders;
use App\Models\TendersVersions;
use App\Models\CategoryProducts;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function search(Request $request)
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();
        
        if( !isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result     = ($type_user == 'demanda') ? $this->getAllProducts() : $this->getAllTenders();
        }
        else if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result     = $this->getTypeCompanyId($request->comunity_id);
        }
        else if( isset($request->type_project) && !isset($request->category_id))
        {
            if($request->type_consult == 'tenders')
            {
                $result     = (($type_user == 'oferta') && isset($request->comunity_id))
                    ? $this->getTendersByProjects($request->type_project , $request->comunity_id)
                    : $this->getTendersByProjects($request->type_project , null);
            } else {

                $result     = (($type_user == 'oferta') && isset($request->comunity_id))
                    ? $this->getProjects($request->type_project , $request->comunity_id)
                    : $this->getProjects($request->type_project , null);
            }

        }
        else if(isset($request->comunity_id) && isset($request->type_project) && isset($request->category_id))
        {   
            if(($type_user == 'oferta') && isset($request->comunity_id) )
            {
                // devolver las licitaciones que pertenecan a type_project, a la categoria de la licitacion y al tipo de entidad
                $result   =   $this->getAllTenderList($request->type_project ,$request->category_id, $request->comunity_id);
            }
            else
            {
                // devolver las licitaciones que pertenecan a type_project y a la categoria de la licitacion.
                $result   =   $this->getAllTenderList($request->type_project ,$request->category_id, null);
            }
        }
        else if(!isset($request->type_project) && isset($request->category_id))
        {
            if($type_user == 'demanda' && isset($request->comunity_id))
            {
                // devolver productos de la categoria que pertenescan a ese tipo de entidad
                $result = $this->getProducts($request->category_id , $request->comunity_id);
            }
            else if($type_user == 'demanda')
            {
                // devolver productos de la categoria
                $result = $this->getProducts($request->category_id , null);
            }

            if($type_user == 'oferta' && isset($request->comunity_id))
            {
                // devolver licitaciones de la categoria que pertenescan a ese tipo de entidad
                $result = $this->getTenders($request->category_id , $request->comunity_id);
            }
            else if($type_user == 'oferta')
            {
                $result = $this->getTenders($request->category_id , null);
            }
        }
        return $result;
    }

    public function getAllTenderList($type_project_id, $category_id, $comunity_id)
    {
        // tender
        $tendersPublish  = $this->getTendersLastVersionPublish(); // ids de ultimas licitaciones en estado publicado
        $tendersCategory = $this->getCategoriesTendersIds($category_id); // ids de licitaciones relacionadas a cierta categoria/s

        $tender_ids      = array_unique(array_merge(json_decode($tendersPublish), json_decode($tendersCategory)));
        $tenders         = Tenders::WhereIn('id', $tender_ids);
        
        //projects
        $projectsIds     = $this->getTypeProjectToProjectIds($type_project_id);
        $tenders         = $tenders->WhereIn('project_id', $projectsIds);

        //entities to company
        if(isset($comunity_id)){
            $companiesIds   = $this->getEntityByCompanies($comunity_id);
            $tenders        = $tenders->whereIn('company_id', $companiesIds);
        }

        $tenders = $tenders->get();

        return $tenders;
    }

    public function getAllProducts()
    {
        $products = Products::where('status', Products::PRODUCT_PUBLISH)
                                        ->orderBy('id', 'desc')
                                        ->get();

        foreach( $products as $key => $product ){
            $product->user['url'] = $product->user->image ? url( 'storage/' . $product->user->image->url ) : null;
            $product->company;
            $product->company->image;
        }

        return $this->showAllPaginate($products);
    }

    public function getAllTenders()
    {
        $tenderLastVersionsPublish  = $this->getTendersLastVersionPublish();
        $tenders                    = Tenders::WhereIn('id', $tenderLastVersionsPublish)->get();

        return $this->showAllPaginate($tenders); 
    }

    public function getTypeProjectToProjectIds($type_project_id)
    {
        $childs = $this->getProjectIdList($type_project_id);

        $projectChildIds = array_column($childs, 'id');

        $type_project_ids = TypeProject::select('projects_type_project.projects_id')
            ->whereIn('type_projects.id',$projectChildIds)
            ->where('type_projects.status',TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects_type_project.projects_id');

        return $type_project_ids;
    }

    public function getProjects($type_project_id, $comunity_id)
    {
        $project_ids = $this->getTypeProjectToProjectIds($type_project_id);
        
        $projects = Projects::whereIn('id', $project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE);

        if(isset($comunity_id))
        {
            $companies  = $this->getEntityByCompanies($comunity_id);
            $projects   = $projects->whereIn('company_id', $companies);
        }

        $projects = $projects->get();

        return $this->showAllPaginate($projects); 
    }

    public function getEntityByCompanies($comunity_id)
    {
        $companies = Company::select('companies.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->where('companies.type_entity_id', $comunity_id)
            ->distinct('companies.id')
            ->pluck('companies.id');

        return $companies;
    }

    public function getTendersByProjects($type_project_id, $comunity_id)
    {
        //tenders
        $tendersPublish = $this->getTendersLastVersionPublish();
        $tenders        = Tenders::whereIn('id', $tendersPublish);

        //projects 
        $project_ids    = $this->getTypeProjectToProjectIds($type_project_id);
        $projects       = Projects::whereIn('id', $project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE)
            ->get();

        $projects       = json_decode( json_encode($projects), true);

        $tenders        = $tenders->whereIn('project_id', $projects);

        if(isset($comunity_id))
        {
            $companies  = $this->getEntityByCompanies($comunity_id);
            $tenders    = $tenders->whereIn('company_id', $companies);
        };

        $tenders = $tenders->get();

        return $this->showAllPaginate($tenders);
    }

    public function getCategoriesTendersIds($category_id)
    {
        $childs = $this->getCategoryIdList($category_id);

        $categoryChildIds = array_column($childs, 'id');

        $categories_ids = Category::select('category_tenders.tenders_id')
            ->whereIn('categories.id',$categoryChildIds)
            ->where('categories.status',Category::CATEGORY_PUBLISH)
            ->join('category_tenders','category_tenders.category_id','=','categories.id')
            ->distinct('category_tenders.tenders_id')
            ->pluck('category_tenders.tenders_id');

        return $categories_ids;
    }

    public function getTenders($category_id, $comunity_id)
    {
        $tendersPublish  = $this->getTendersLastVersionPublish(); // ids de ultimas licitaciones en estado publicado
        $tendersCategory = $this->getCategoriesTendersIds($category_id); // ids de licitaciones relacionadas a cierta categoria/s

        // une ambas cadenas de arrays de tendersCategory y de tendersPublish, quita los ids repetidos y deja uno solo de cada uno
        $tender_ids      = array_unique(array_merge(json_decode($tendersCategory), json_decode($tendersPublish)));
        $tenders         = Tenders::whereIn('id',$tender_ids)->get();

        if(isset($comunity_id))
        {
            $companiesIds   = $this->getEntityByCompanies($comunity_id);
            $tenders        = $tenders->whereIn('company_id', $companiesIds);
        };

        return $this->showAllPaginate($tenders); 
    }

    public function getCategoriesProductsIds($category_id)
    {
        $childs             = $this->getCategoryIdList($category_id);
        $categoryChildIds   = array_column($childs, 'id');
        $categories_ids     = Category::select('category_products.products_id')
            ->whereIn('categories.id',$categoryChildIds)
            ->where('categories.status',Category::CATEGORY_PUBLISH)
            ->join('category_products','category_products.category_id','=','categories.id')
            ->distinct('category_products.products_id')
            ->pluck('category_products.products_id');

        return $categories_ids;
    }

    public function getProducts($category_id, $comunity_id)
    {
        $products_id     = $this->getCategoriesProductsIds($category_id);

        $products = Products::whereIn('id', $products_id)
            ->where('status', Products::PRODUCT_PUBLISH);

        if(isset($comunity_id))
        {
            $companiesIds   = $this->getEntityByCompanies($comunity_id);
            $products       = $products->whereIn('company_id', $companiesIds);
        }

        $products = $products->get();

        return $this->showAllPaginate($products); 

    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------
    public function index(Request $request)
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();

        if( !isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result['company_list']             = $this->getTypeCompanyAll();

            if($type_user == 'demanda'){
                $result['category_product_list']    = $this->getCategoryItemList($this->getCategoryProductPublish());
            }

            if($type_user == 'oferta'){
                $result['project_tender_list']      = $this->getProjectItemList();
                $result['category_tender_list']     = $this->getCategoryItemList($this->getCategoryTenderPublish());
            }
        }
        else if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result['company_list']             = $this->getTypeCompanyId($request->comunity_id);
        }
        else if((isset($request->type_project) || isset($request->comunity_id)) && !isset($request->category_id))
        {
            if($request->comunity_id)
                $result['company_list']         = $this->getTypeCompanyId($request->comunity_id);

            if(($request->type_project) && ($request->type_consult == 'projects'))
                $result['project_list']         = $this->getProjectIdList($request->type_project);

            if(($request->type_project) && ($request->type_consult == 'tenders'))
                $result['project_tender_list']  = $this->getTenderProjectIdList($request->type_project); 
        }
        else if((isset($request->category_id) || isset($request->comunity_id)) && !isset($request->type_project))
        {
            if($request->comunity_id)
                $result['company_list']         = $this->getTypeCompanyId($request->comunity_id);

            if(($request->category_id) && ($type_user == 'demanda'))
                $result['category_list']        = $this->getCategoryIdList($request->category_id);

            if(($request->category_id) && ($type_user == 'oferta'))
                $result['category_tender_list'] = $this->getTenderCategoryIdList($request->category_id);
        }

        return $result;
    }

    public function getTypeCompanyAll()
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $types_entities = Company::select('types_entities.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->distinct('types_entities.id')
            ->orderBy('name','ASC')
            ->get();

        $array = [];

        foreach($types_entities as $type_entity) {
            $array[] = array(
                "id"        => $type_entity->id,
                "name"      => $type_entity->name,
                "slug"      => $type_entity->slug,
                "status"    => $type_entity->status,
                "entities"  => DB::select('call get_child_type_entity("'.$type_entity->id.'")')
            );
        }

        return $array;
    }

    public function getCategoryItemList($catgoryItem)
    {
        $categoryParents        = $this->getCategoryParents();
        $arryIdCatDadtoChild    = $this->getArrCatProductPublish($catgoryItem);

        $arr = [];
        foreach( $this->getChildCatProduct($categoryParents) as $key_parent => $parent){
            foreach($parent as $key_child => $child) {
                if (in_array($child['id'], $arryIdCatDadtoChild)){
                    $arr[$key_parent][] = $child;
                };
            };
        }; 

        return array_values($arr);
    }

    public function getProjectItemList()
    {
        $projectParents     = $this->getProjectParents();
        $tenders            = $this->getTendersLastVersionPublish();

        $arrIdTypeProjects  = Tenders::select('type_projects.*')
            ->whereIn('tenders.id',$tenders)
            ->join('projects','projects.id','=','tenders.project_id')
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->join('projects_type_project','projects_type_project.projects_id','=','projects.id')
            ->join('type_projects','type_projects.id','=','projects_type_project.type_project_id')
            ->where('type_projects.status', TypeProject::TYPEPROJECT_PUBLISH)
            ->distinct('type_projects.id')
            ->pluck('type_projects.id');

        $arrIdTypeProjects = $this->getArrTypeProjets($arrIdTypeProjects);

        $arr = [];
        foreach( $this->getChildTypeProyect($projectParents) as $key_parent => $parent){
            foreach($parent as $key_child => $child) {
                if (in_array($child['id'], $arrIdTypeProjects)){
                    $arr[$key_parent][] = $child;
                };
            };
        };

        return array_values($arr);
    }

    public function getCategoryProductPublish()
    {
        $categoryProductPublish = CategoryProducts::select('category_products.category_id')
        ->join('products','products.id','=','category_products.products_id')
            ->where('products.status',Products::PRODUCT_PUBLISH)
            ->distinct('category_products.category_id')
            ->pluck('category_products.category_id');
            
        return $categoryProductPublish;
    }

    public function getCategoryTenderPublish()
    {
        $tenders = $this->getTendersLastVersionPublish();
        
        $categoryTenderPublish = CategoryTenders::select('category_id')
        ->whereIn('tenders_id', $tenders)
            ->distinct('category_id')
            ->pluck('category_id');
            
            return $categoryTenderPublish;
    }

    public function getArrCatProductPublish($ids) {

        $childs     = $this->getChildCatProduct($ids);
        $array_id   = [];

        foreach($childs as $rows)
        {
            foreach($rows as $columns)
            {
                $array_id[] = $columns['id'];
            };
        };

        $array_id = array_unique($array_id);

        return $array_id;
    }

    public function getArrTypeProjets($ids)
    {
        $childs     = $this->getChildTypeProyect($ids);
        $array_id   = [];

        foreach($childs as $rows)
        {
            foreach($rows as $columns)
            {
                $array_id[] = $columns['id'];
            };
        };

        $array_id = array_unique($array_id);

        return $array_id;
    }

    public function getCategoryParents(){
        $parents = Category::whereNull('parent_id')
        ->where('status', Category::CATEGORY_PUBLISH)
        ->orderBy('name','ASC')
        ->pluck('id');
        
        return $parents;
    }

    public function getProjectParents()
    {
        $parents = TypeProject::whereNull('parent_id')
            ->where('status', TypeProject::TYPEPROJECT_PUBLISH)
            ->orderBy('name','ASC')
            ->pluck('id');

        return $parents;
    }
    
    public function getChildCatProduct($lists){
        
        $array = [];
        foreach($lists as $list) {
            $childs = DB::select('call get_child_type_categoty("'.$list.'")');
            $array[] = json_decode( json_encode($childs), true);
        }
        
        return $array;
    }

    public function getChildTypeProyect($lists){
        
        $array = [];
        foreach($lists as $list) {
            $childs = DB::select('call get_child_type_project("'.$list.'")');
            $array[] = json_decode( json_encode($childs), true);
        }
        
        return $array;
    }

    public function getTendersLastVersionPublish()
    {
        $tenders = TendersVersions::select(DB::raw('max(created_at), tenders_id'))
            ->where('status',TendersVersions::LICITACION_PUBLISH)
            ->groupBy('tenders_id')
            ->pluck('tenders_id');

        return $tenders;
    }

    public function getTypeCompanyId($id)
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $companies = Company::select('companies.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.id', $id)
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->distinct('companies.id')
            ->orderBy('companies.name','ASC')
            ->get();

        return $this->showAllPaginate($companies);
    }

    // public function getTypeCompanyId($id)
    // {
    //     $user = $this->validateUser();

    //     $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
    //     $types_entities = Company::select('types_entities.*')
    //         ->where('companies.status',Company::COMPANY_APPROVED)
    //         ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
    //         ->where('types_entities.id', $id)
    //         ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
    //         ->join('types', 'types_entities.type_id', '=', 'types.id')
    //         ->where('types.slug', $type_slug)
    //         ->distinct('types_entities.id')
    //         ->orderBy('name','ASC')
    //         ->get();

    //     $array = [];

    //     foreach($types_entities as $type_entity) {
    //         $array[] = array(
    //             "id"        => $type_entity->id,
    //             "name"      => $type_entity->name,
    //             "slug"      => $type_entity->slug,
    //             "status"    => $type_entity->status,
    //             "entities"  => DB::select('call get_child_type_entity("'.$type_entity->id.'")')
    //         );
    //     }

    //     return $array;
    // }

    public function getProjectIdList($id)
    {
        $childs = DB::select('call get_child_type_project("'.$id.'")');
        return json_decode( json_encode($childs), true);
    }

    public function getTenderProjectIdList($id)
    {
        $tenders    = $this->getTendersLastVersionPublish();

        $tenders    = Tenders::whereIn('id',$tenders)
            ->where('project_id',$id)
            ->get();
        
        return $this->showAllPaginate($tenders);
    }

    public function getCategoryIdList($id)
    {
        $childs = DB::select('call get_child_type_categoty("'.$id.'")');
        return json_decode( json_encode($childs), true);
    }

    public function getTenderCategoryIdList($id)
    {
        $tenders = $this->getTendersLastVersionPublish();

        $tenders = CategoryTenders::select('tenders.*')
            ->where('category_tenders.category_id', $id)
            ->join('tenders','category_tenders.tenders_id','=','tenders.id')
            ->whereIn('tenders.id', $tenders)
            ->distinct('tenders.id')
            ->pluck('tenders.id');

        $tenders = Tenders::whereIn('id',$tenders)->get();

        return $this->showAllPaginate($tenders);
    }
      
}
