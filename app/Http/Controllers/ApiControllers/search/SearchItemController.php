<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use Carbon\Carbon;
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

        $filters = [];
        if( isset($request->status) )
            $filters['status'] = $request->status;
        if( isset($request->date) )
            $filters['date'] = $request->date;
        if( isset($request->date_end) )
            $filters['date_end'] = $request->date_end;
        
        $result = [];

        // Si no viene ningún FILTRO
        if( !isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result = ($type_user == 'demanda') ? $this->getAllProducts() : $this->getAllTenders($filters);
        }
        // Si vienen los 3 FILTROS
        else if(isset($request->comunity_id) && isset($request->type_project) && isset($request->category_id))
        {
            // devolver las licitaciones que pertenecan a type_project, a la categoria de la licitacion y al tipo de entidad
            $result = $this->getAllTenderList($request->type_project ,$request->category_id, $request->comunity_id);
        }
        // Si solo viene Entidad
        else if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result = $this->getTypeCompanyId($request->comunity_id);
        }
        // Si viene categoría y no viene tipo de proyecto con o sin Comunidad
        else if( !isset($request->type_project) && isset($request->category_id))
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
                $result = $this->getTenders($request->category_id , $request->comunity_id, $filters);
            }
            else if($type_user == 'oferta')
            {
                $result = $this->getTenders($request->category_id , null, $filters);
            }
        }
        // Si viene tipo de proyecto y no viene categorías con o sin Comunidad
        else if( isset($request->type_project) && !isset($request->category_id))
        {
            if($request->type_consult == 'tenders')
            {
                $result     = (($type_user == 'oferta') && isset($request->comunity_id))
                    ? $this->getTendersByProjects($request->type_project , $request->comunity_id, $filters)
                    : $this->getTendersByProjects($request->type_project , null, $filters);
            } else {
                $result     = (($type_user == 'oferta') && isset($request->comunity_id))
                    ? $this->getProjects($request->type_project , $request->comunity_id, $filters)
                    : $this->getProjects($request->type_project , null, $filters);
            }
        }
        // Si viene Tipo de proyecto y Categoria
        else if(!isset($request->comunity_id) && isset($request->type_project) && isset($request->category_id)){
            // devolver las licitaciones que pertenecan a type_project y a la categoria de la licitacion.
            $result = $this->getAllTenderList($request->type_project ,$request->category_id, null);
        }

        return $result;
    }

    public function getAllTenderList($type_project_id, $category_id, $comunity_id)
    {
        // tender
        $tendersPublish  = $this->getTendersLastVersionPublish(); // ids de ultimas licitaciones en estado publicado
        $tender_ids = [];

        if( $category_id !='all' ){
            $tendersCategory = $this->getCategoriesTendersIds($category_id); // ids de licitaciones relacionadas a cierta categoria/s
    
            // $tender_ids      = array_unique(array_merge(json_decode($tendersPublish), json_decode($tendersCategory)));
            foreach ($tendersCategory as $key => $id) {
                if( in_array( $id, json_decode($tendersPublish) ) )
                    $tender_ids[] = $id;
            }
        }else{
            $tender_ids = $tendersPublish;
        }

        $tenders = Tenders::WhereIn('id', $tender_ids);
        
        //projects
        if( $type_project_id != 'all' ){
            $projectsIds     = $this->getTypeProjectToProjectIds($type_project_id);
            $tenders         = $tenders->WhereIn('project_id', $projectsIds);
        }

        //entities to company
        if(isset($comunity_id) && $comunity_id != 'all'){
            $companiesIds   = $this->getEntityByCompanies($comunity_id);
            $tenders        = $tenders->whereIn('company_id', $companiesIds);
        }

        $tenders = $tenders->get();
        $tenders = $this->addTagsTenders($tenders);
        
        return $this->showAllPaginate($tenders);
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

    public function getAllTenders($filters)
    {
        $tenderLastVersionsPublish  = $this->getTendersLastVersionPublish();
        $tenders                    = Tenders::WhereIn('id', $tenderLastVersionsPublish);

        //filtro de fechas, recibe solo una fecha inicial o la inicial y la final.
        $tenders                    = $this->getTenderFiltegetTendersrByDate($tenders, $filters);

        $tenders                    = $tenders->get();
        $tenders                    = $this->addTagsTenders($tenders);

        return $this->showAllPaginate($tenders); 
    }

    public function getTenderFiltegetTendersrByDate($tender, $filters)
    {
        if(!isset($filters['date']) && !isset($filters['date_end']))
        {
            return $tender;
        }
        else if(isset($filters['date']) && !isset($filters['date_end']))
        {
            $start_date     = Carbon::createFromFormat('Y-m-d', $filters['date']);
            $end_date       = null;
        }
        else if(isset($filters['date']) && isset($filters['date_end']))
        {
            $start_date     = Carbon::createFromFormat('Y-m-d', $filters['date']);
            $end_date       = Carbon::createFromFormat('Y-m-d', $filters['date_end']);
        }

        $tenders  = $tender->get();

        $tenderVersionLastIds = [];
        foreach ($tenders as $key => $tender)
        {
            $tenderVersionLastIds[] = $tender->tendersVersionLast()->id;
        };

        $tenderVersionLast = TendersVersions::select('tenders_id')->whereIn('id',$tenderVersionLastIds);

        $tenderVersionLast = (isset($end_date)) ? 
            $tenderVersionLast->whereBetween('date',[ $start_date, $end_date]) : // existe la fecha inicial y final
            $tenderVersionLast->where('date','>=', $start_date); // solo existe la fecha inicial

        $tenderVersionLast = $tenderVersionLast->pluck('tenders_id');

        return Tenders::whereIn('id', $tenderVersionLast);
    }

    public function getTypeProjectToProjectIds($type_project_id)
    {
        $childs = [];
        if( $type_project_id == 'all' ){
            $typesprojects = $this->getProjectItemList(null,null,null);
            $idsTypeProjects = [];
            foreach ($typesprojects as $key => $parent) {
                $childs = array_merge( $childs, $this->getProjectIdChildList($parent[0]['id']));
            }
        }else{
            $childs = $this->getProjectIdChildList($type_project_id);
        }
        
        $projectChildIds = array_column($childs, 'id');

        $type_project_ids = TypeProject::select('projects_type_project.projects_id')
            ->whereIn('type_projects.id',$projectChildIds)
            ->where('type_projects.status',TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects_type_project.projects_id');

        return $type_project_ids;
    }

    public function getProjects($type_project_id, $comunity_id, $filters )
    {
        $project_ids = $this->getTypeProjectToProjectIds($type_project_id);
        
        $projects = Projects::whereIn('id', $project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE);
        
        $projects = $this->statusProjects($projects, $filters);
        $projects = $this->dateProjects($projects, $filters);

        if(isset($comunity_id) && $comunity_id != 'all')
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

    public function getTendersByProjects($type_project_id, $comunity_id, $filters)
    {
        //tenders
        $tendersPublish = $this->getTendersLastVersionPublish();
        $tenders        = Tenders::whereIn('id', $tendersPublish);

        //projects 
        $project_ids    = $this->getTypeProjectToProjectIds($type_project_id);

        $projects       = Projects::whereIn('id', $project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE);
            
        $projects = $this->statusProjects($projects, $filters);
        $projects = $this->dateProjects($projects, $filters);

        $projects = $projects->get();
        $projects       = json_decode( json_encode($projects), true);

        $tenders        = $tenders->whereIn('project_id', $projects);

        if(isset($comunity_id) && $comunity_id!='all')
        {
            $companies  = $this->getEntityByCompanies($comunity_id);
            $tenders    = $tenders->whereIn('company_id', $companies);
        };

        $tenders = $tenders->get();
        $tenders = $this->addTagsTenders($tenders);
        // 
        
        return $this->showAllPaginate($tenders);
    }
    
    public function addTagsTenders( $tenders ){
        foreach ( $tenders as $key => $tender) {
            $tendersPublish = $tender->tendersVersionLastPublish();
            if( $tendersPublish ){
                $tender->tags = $tendersPublish->tags;
            }
        }

        return $tenders;
    }

    public function statusProjects( $projects, $filters ){
        if( $filters && isset($filters['status'])){
            if( $filters['status'] == 'especificaciones-tecnicas' ){
                $projects = $projects->where('status','=', 'especificaciones-tecnicas');
            }elseif( $filters['status'] == 'en-construccion' ){
                $projects = $projects->where('status','=', 'en-construccion');
            }
        }

        return $projects;
    }

    public function dateProjects( $projects, $filters ){
        if( $filters && (isset($filters['date']) || isset($filters['date_end'])) ){
            $date_start = $date_end = '';
            if( isset($filters['date']) && $filters['date'] != 'null' )
                $date_start = Carbon::createFromFormat('Y-m-d', $filters['date'])->format('Y-m-d');
            if( isset($filters['date_end']) && $filters['date_end'] != 'null' )
                $date_end = Carbon::createFromFormat('Y-m-d', $filters['date_end'])->format('Y-m-d');

            if( $date_start && $date_end ){
                $projects = $projects->where(function($query) use ($date_start,$date_end){
                    $query->whereBetween('date_start', [$date_start, $date_end])
                          ->orWhereBetween('date_end', [$date_start, $date_end]);
                });
            }elseif( $date_start ){
                $projects->where('date_start','<=', $date_start)
                         ->where('date_end','>=', $date_start);
            }elseif( $date_end ){
                $projects->where('date_start','<=', $date_end)
                         ->where('date_end','>=', $date_end);
            }
        }

        return $projects;
    }

    public function getCategoriesTendersIds($category_id)
    {
        $childs = $this->getCategoryIdChildList($category_id);

        $categoryChildIds = array_column($childs, 'id');

        $categories_ids = Category::select('category_tenders.tenders_id')
            ->whereIn('categories.id',$categoryChildIds)
            ->where('categories.status',Category::CATEGORY_PUBLISH)
            ->join('category_tenders','category_tenders.category_id','=','categories.id')
            ->distinct('category_tenders.tenders_id')
            ->pluck('category_tenders.tenders_id');

        return $categories_ids;
    }

    public function getTenders($category_id, $comunity_id, $filters)
    {
        $tendersPublish  = $this->getTendersLastVersionPublish(); // ids de ultimas licitaciones en estado publicado
        $tender_ids = [];

        if( $category_id != 'all' ){
            $tendersCategory = $this->getCategoriesTendersIds($category_id); // ids de licitaciones relacionadas a cierta categoria/s
            // une ambas cadenas de arrays de tendersCategory y de tendersPublish, quita los ids repetidos y deja uno solo de cada uno
            foreach ($tendersCategory as $key => $id) {
                if( in_array( $id, json_decode($tendersPublish) ) )
                    $tender_ids[] = $id;
            }
        }else{
            $tender_ids = $tendersPublish;
        }

        $tenders         = Tenders::whereIn('id',$tender_ids);  

        if(isset($comunity_id) && $comunity_id != 'all')
        {
            $companiesIds   = $this->getEntityByCompanies($comunity_id);
            $tenders        = $tenders->whereIn('company_id', $companiesIds);
        };

        $tenders         = $this->getTenderFiltegetTendersrByDate($tenders, $filters);
        $tenders         = $tenders->get();

        $tenders = $this->addTagsTenders($tenders);
        
        return $this->showAllPaginate($tenders); 
    }

    public function getCategoriesProductsIds($category_id)
    {
        $childs             = $this->getCategoryIdChildList($category_id);
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
        if( $category_id == 'all' ){
            $products = Products::where('status', Products::PRODUCT_PUBLISH);
        }else{
            $products_id     = $this->getCategoriesProductsIds($category_id);
            $products = Products::whereIn('id', $products_id)
                ->where('status', Products::PRODUCT_PUBLISH);
        }

        if(isset($comunity_id) && $comunity_id != 'all')
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
        $result = [];
        
        $result['company_list'] = $this->getTypeCompanyFilters($type_user, $request->comunity_id,$request->type_project, $request->category_id);

        if($type_user == 'demanda'){
            $result['category_product_list']    = $this->getCategoryItemList($type_user, $request->comunity_id, $request->type_project, $request->category_id);
        }

        if($type_user == 'oferta'){
            $result['project_tender_list']      = $this->getProjectItemList($request->comunity_id,$request->type_project, $request->category_id);
            $result['category_tender_list']     = $this->getCategoryItemList($type_user, $request->comunity_id, $request->type_project, $request->category_id);
        }

        return $result;
    }

    public function getTypeCompanyFilters( $type, $comunity_id = '', $type_project = '', $category_id = '' )
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';
        
        $types_entities = Company::select('types_entities.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug);
            
        
        if( isset($comunity_id) && $comunity_id != 'all' ){
            $types_entities = $types_entities->where('types_entities.id', '=', $comunity_id);
        }

        if( isset($type_project) && $type_project != 'all' ){
            $typesProjectsIds = $this->getArrTypeProjets( [$type_project] );

            $types_entities = $types_entities->join('projects', 'projects.company_id', '=', 'companies.id')
                            ->join('projects_type_project', 'projects_type_project.projects_id', '=', 'projects.id')
                            ->whereIn( 'projects_type_project.type_project_id', $typesProjectsIds );
        }

        if( isset($category_id) && $category_id != 'all' ){
            if( $type == 'demanda' ){
                $arryIdCatDadtoChild = $this->getArrCatProductPublish($this->getCategoryProductPublish());
            }elseif( $type == 'oferta' ){
                $arryIdCatDadtoChild = $this->getArrCatProductPublish($this->getCategoryTenderPublish());
            }

            $tender_ids = [];
            foreach ($arryIdCatDadtoChild as $key => $id) {
                $tender_ids[] = $id;
            }
            
            $categories_ids = [];
            foreach( $this->getChildCatProductAndTenders([$category_id]) as $key_parent => $parent){
                foreach($parent as $key_child => $child) {
                    if (in_array($child['id'], $tender_ids)){
                        $categories_ids[] = $child['id'];
                    };
                };
            };
            
            if( $type == 'demanda' ){
                $types_entities = $types_entities->join('products', 'products.company_id', '=', 'companies.id')
                                ->join('category_products', 'category_products.products_id', '=', 'products.id')
                                ->whereIn( 'category_products.category_id', $categories_ids );
            }elseif( $type == 'oferta' ){
                $types_entities = $types_entities->join('tenders', 'tenders.company_id', '=', 'companies.id')
                                ->join('category_tenders', 'category_tenders.tenders_id', '=', 'tenders.id')
                                ->whereIn( 'category_tenders.category_id', $categories_ids );
            }
        }

        $types_entities = $types_entities->distinct('types_entities.id')
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

    public function getCategoryItemList($type, $comunity_id = '', $type_project = '', $category_id = '')
    {
        if( $type == 'demanda' ){
            $categoryItem = CategoryProducts::select('category_products.category_id')
                                                        ->join('products','products.id','=','category_products.products_id');

            if( isset($comunity_id) && $comunity_id != 'all' ){
                $categoryItem = $categoryItem->join('companies', 'companies.id', '=', 'products.company_id' )
                                        ->where('companies.type_entity_id', '=', $comunity_id);
            }

            if( isset($category_id) && $category_id != 'all' ){
                $arryIdCatDadtoChild = $this->getArrCatProductPublish($this->getCategoryProductPublish());
                $tender_ids = [];
                foreach ($arryIdCatDadtoChild as $key => $id) {
                    $tender_ids[] = $id;
                }
                
                foreach( $this->getChildCatProductAndTenders([$category_id]) as $key_parent => $parent){
                    foreach($parent as $key_child => $child) {
                        if (in_array($child['id'], $tender_ids)){
                            $categories_ids[] = $child['id'];
                        };
                    };
                };
            
                $categoryItem = $categoryItem->whereIn( 'category_products.category_id', $categories_ids );
            }
            
            $categoryItem = $categoryItem->where('products.status',Products::PRODUCT_PUBLISH)
                                        ->distinct('category_products.category_id')
                                        ->pluck('category_products.category_id');

        }elseif( $type == 'oferta' ){
            // $categoryItem = $this->getCategoryTenderPublish();
            $tenders = $this->getTendersLastVersionPublish();
            
            $categoryItem = CategoryTenders::select('category_id')
                ->whereIn('tenders_id', $tenders);
            
            if( isset($comunity_id) || isset($type_project) ){
                $categoryItem = $categoryItem->join('tenders', 'tenders.id', '=', 'category_tenders.tenders_id' );
            }

            if( isset($comunity_id) && $comunity_id != 'all' ){
                $categoryItem = $categoryItem->join('companies', 'companies.id', '=', 'tenders.company_id' )
                                        ->where('companies.type_entity_id', '=', $comunity_id);
            }
            
            if( isset($type_project) && $type_project != 'all' ){
                $typesProjectsIds = $this->getArrTypeProjets( [$type_project] );
                $categoryItem = $categoryItem->join('projects_type_project', 'projects_type_project.projects_id', '=', 'tenders.project_id')
                            ->whereIn( 'projects_type_project.type_project_id', $typesProjectsIds );
            }

            if( isset($category_id) && $category_id != 'all' ){
                $arryIdCatDadtoChild = $this->getArrCatProductPublish($this->getCategoryTenderPublish());
                $tender_ids = [];
                foreach ($arryIdCatDadtoChild as $key => $id) {
                    $tender_ids[] = $id;
                }
                
                foreach( $this->getChildCatProductAndTenders([$category_id]) as $key_parent => $parent){
                    foreach($parent as $key_child => $child) {
                        if (in_array($child['id'], $tender_ids)){
                            $categories_ids[] = $child['id'];
                        };
                    };
                };
            
                $categoryItem = $categoryItem->whereIn( 'category_tenders.category_id', $categories_ids );
            }

            $categoryItem = $categoryItem->distinct('category_id')
                            ->pluck('category_id');
        }

        $categoryParents        = $this->getCategoryParents();
        $arryIdCatDadtoChild    = $this->getArrCatProductPublish($categoryItem);

        $arr = [];
        foreach( $this->getChildCatProductAndTenders($categoryParents) as $key_parent => $parent){
            foreach($parent as $key_child => $child) {
                if (in_array($child['id'], $arryIdCatDadtoChild)){
                    $arr[$key_parent][] = $child;
                };
            };
        }; 

        return array_values($arr);
    }

    public function getProjectItemList( $comunity_id = '', $type_project = '', $category_id = '' )
    {
        $projectParents     = $this->getProjectParents();
        $tenders            = $this->getTendersLastVersionPublish();

        $arrIdTypeProjects = TypeProject::select('type_projects.*')
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->join('projects', 'projects_type_project.projects_id', '=', 'projects.id')
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->where('type_projects.status', TypeProject::TYPEPROJECT_PUBLISH);

        if( isset($comunity_id) && $comunity_id != 'all' ){
            $arrIdTypeProjects = $arrIdTypeProjects->join('companies', 'companies.id', '=', 'projects.company_id' )
                                    ->where('companies.type_entity_id', '=', $comunity_id);
        }

        if( isset($type_project) && $type_project != 'all' ){
            $typesProjectsIds = $this->getArrTypeProjets( [$type_project] );
            $arrIdTypeProjects = $arrIdTypeProjects->whereIn('projects_type_project.type_project_id', $typesProjectsIds);
        }

        if( isset($category_id) && $category_id != 'all' ){
            $arryIdCatDadtoChild = $this->getArrCatProductPublish($this->getCategoryTenderPublish());
            $tender_ids = [];
            foreach ($arryIdCatDadtoChild as $key => $id) {
                $tender_ids[] = $id;
            }
            
            $categories_ids = [];
            foreach( $this->getChildCatProductAndTenders([$category_id]) as $key_parent => $parent){
                foreach($parent as $key_child => $child) {
                    if (in_array($child['id'], $tender_ids)){
                        $categories_ids[] = $child['id'];
                    };
                };
            };
            
            $arrIdTypeProjects = $arrIdTypeProjects->join('tenders', 'tenders.project_id', '=', 'projects.id')
                            ->join('category_tenders', 'category_tenders.tenders_id', '=', 'tenders.id')
                            ->whereIn( 'category_tenders.category_id', $categories_ids );
        }
        
        $arrIdTypeProjects = $arrIdTypeProjects->distinct('type_projects.id')
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

        $childs     = $this->getChildCatProductAndTenders($ids);
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
        $childs = [];
        if( count($ids) && $ids[0] == 'all' ){
            
        }else{
            $childs = $this->getChildTypeProyect($ids);
        }
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
    
    public function getChildCatProductAndTenders($lists){
        $array = [];
        foreach($lists as $list) {
            if($list){
                $childs = DB::select('call get_child_type_categoty("'.$list.'")');
                $array[] = json_decode( json_encode($childs), true);
            }
        }
        
        return $array;
    }

    public function getChildTypeProyect($lists){
        
        $array = [];
        foreach($lists as $list) {
            if( $list != 'all' ){
                $childs = DB::select('call get_child_type_project("'.$list.'")');
                $array[] = json_decode( json_encode($childs), true);
            }
        }
        
        return $array;
    }

    public function getTendersLastVersionPublish()
    {
        $tenders = DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status',TendersVersions::LICITACION_PUBLISH)
            ->where((function($query)
            {
                $query->select(DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where (`b`.`status` = '".TendersVersions::LICITACION_FINISHED."' 
                    or `b`.`status` = '".TendersVersions::LICITACION_CLOSED."') 
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');

        return $tenders;
    }

    public function getTypeCompanyId($id)
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $companies = Company::select('companies.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id');

        if( $id != 'all' ){
            $companies = $companies->where('types_entities.id', $id);
        }

        $companies = $companies->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->distinct('companies.id')
            ->orderBy('companies.name','ASC')
            ->get();

        return $this->showAllPaginate($companies);
    }

    public function getProjectIdChildList($id)
    {
        $childs = DB::select('call get_child_type_project("'.$id.'")');

        foreach ($childs as $key => $child)
        {
            if($id > $child->id)
                unset($childs[$key]);
        };

        return json_decode( json_encode($childs), true);
    }

    public function getCategoryIdChildList($id)
    {
        $childs = DB::select('call get_child_type_categoty("'.$id.'")');

        foreach ($childs as $key => $child)
        {
            if($id > $child->id)
                unset($childs[$key]);
        };

        return json_decode( json_encode($childs), true);
    }
      
}
