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
            if($type_user == 'demanda'){
                $result['product_list'] = $this->getAllProducts();
            }
            if($type_user == 'oferta'){
                $result['tenders_list'] = $this->getAllTenders();
            }
        }
        else if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            // if($type_user == 'demanda'){
            //     // devolver compañias de ofertas del id
            // }
            // if($type_user == 'oferta'){
            //     // devolver compañias demanda de id
            // }
            $result['company_list']     = $this->getTypeCompanyId($request->comunity_id);
        }
        else if( isset($request->comunity_id) && isset($request->type_project) && !isset($request->category_id))
        {
            if(($type_user == 'oferta') && isset($request->comunity_id) ){
                // devolver proyectos con el tipo de proyecto seleccionado y pertenscan a ese cominuy_id
                $result['project_list'] = $this->getProjects($request->type_project , $request->comunity_id);//pendiente 
            } else {
                // devolver proyectos con el tipo de proyectos seleccionados.
                $result['project_list'] = $this->getProjects($request->type_project , null);
            }
        }
        else if(isset($request->type_project) && isset($request->category_id))
        {
            if(($type_user == 'oferta') && isset($request->comunity_id) ){
                // devolver las licitaciones que pertenecan a type_project,a la categoria de la licitacion y al tipo de entidad
            } else {
                // devolver las licitaciones que pertenecan a type_project y a la categoria de la licitacion.
            }
        }
        // else if(!isset($request->type_project) && isset($request->category_id))
        // {
        //     if($type_user == 'demanda' && isset($request->comunity_id))
        //     {
        //         // devolver productos de la categoria que pertenescan a ese tipo de entidad
        //     }
        //     else if($type_user == 'demanda')
        //     {
        //         // devolver productos de la categoria
        //     }
        //     if($type_user == 'oferta' && isset($request->comunity_id))
        //     {
        //         // devolver licitaciones de la categoria que pertenescan a ese tipo de entidad
        //     }
        //     else if($type_user == 'oferta')
        //     {
        //         // devolver licitaciones de la categoria
        //     }
        // }
        return $result;
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

    public function getProjects($type_project_id, $comunity_id)
    {
        $type_project_ids = TypeProject::select('projects_type_project.projects_id')
            ->where('type_projects.id',$type_project_id)
            ->where('type_projects.status',TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects_type_project.projects_id');
        
        $projects = Projects::whereIn('id', $type_project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE);

        if(isset($comunity_id)){
            $projects = $projects->where('visible', Projects::PROJECTS_VISIBLE);
        }

        $projects = $projects->get();

        return $this->showAllPaginate($projects); 
    }

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
        
        $types_entities = Company::select('types_entities.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.id', $id)
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
