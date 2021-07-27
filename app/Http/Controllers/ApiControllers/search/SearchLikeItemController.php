<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Addresses;
use App\Models\Projects;
use App\Models\TendersVersions;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Brands;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchLikeItemController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $user           = $this->validateUser();
        $type_user      = $user->userType();

        $search_item    = $request->search_item;
        $type_consult   = $request->type_consult;

        $result = "";
        if($type_user == 'oferta')
        {
            if(isset($type_consult))
            {
                if($type_consult == 'companies')
                {
                    //Busqueda por las compañias
                    $result = $this->getCompanies($search_item);
                }
                else if($type_consult == 'projets')
                {
                    //Busca por los proyectos
                    $result = $this->getProjects($search_item);
                }
                else if($type_consult == 'tenders')
                {
                    //Busca por las licitaciones
                    $result = $this->getTenders($search_item);
                }
            }
            else if(!isset($type_consult))
            {
                $result = $this->getTenders($search_item);
            }
        }
        else
        {
            if(isset($type_consult))
            {
                if($type_consult == 'companies')
                {
                    //Busqueda por las compañias
                    $result = $this->getCompanies($search_item);
                }
                else if($type_consult == 'products')
                {
                    //Busca por los productos
                    $result = $this->getProducts($search_item);
                }
            }
            else if(!isset($type_consult))
            {
                //Busca por los productos
                $result = $this->getProducts($search_item);
            }

        };

        return $result;
    }

    public function getCompanies($like)
    {
        //buscar la compañia por medio de la dirección
        $companyAddress         = $this->getCompanyAddress($like);
        //busca la compañia por medio del nombre o descripción de la compañia
        $companyNameDescript    = $this->getCompanyNameDescription($like);

        $company_id             = array_unique(array_merge(json_decode($companyAddress), json_decode($companyNameDescript)));

        $companies              = Company::whereIn('id', $company_id)->orderBy('name', 'asc')->get();

        return $this->showAllPaginate($companies);
    }

    public function getCompanyAddress($like)
    {
        $user      = $this->validateUser();
        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';

        $companies = Addresses::select('addresses.addressable_id')
            ->where('addresses.addressable_type', Company::class)
            ->where(strtolower('addresses.address'),'LIKE','%'.strtolower($like).'%')
            ->join('companies','companies.id','=','addresses.addressable_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->pluck('addresses.addressable_id');

        return $companies;
    }

    public function getCompanyNameDescription($like)
    {
        $user      = $this->validateUser();
        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';

        $companies = Company::select('companies.id')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->where( function($query) use ($like){
                $query->where(strtolower('companies.name'),'LIKE','%'.strtolower($like).'%')
                ->orWhere(strtolower('companies.description'),'LIKE','%'.strtolower($like).'%');
            })
            ->join('types_entities','types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->pluck('companies.id');

        return $companies;
    }

    public function getProjects($like)
    {
        //busca por el nombre/descripción del proyecto
        $projetName         = $this->getNameDescriptionProject($like);
        //busca proyectos relacionados con el nombre del tipo de proyecto
        $projetTypeProject  = $this->getTypeProjects($like);
        //busca proyectos relacionados con el nombre del tipo de proyecto
        $projetAddress      = $this->getProjectAddress($like);

        //hace un merge de $projetName y $projetTypeProject, quita los id repetidos, dejando uno de cada uno
        $projects_ids       = array_unique(array_merge(json_decode($projetName), json_decode($projetAddress) , json_decode($projetTypeProject)));

        $projects           = Projects::whereIn('id', $projects_ids)->orderBy('name', 'asc')->get();

        return $this->showAllPaginate($projects);
    }


    public function getNameDescriptionProject($like)
    {
        $projets = Projects::select('id')
            ->where('visible', Projects::PROJECTS_VISIBLE)
            ->where( function($query) use ($like){
                $query->where(strtolower('name'),'LIKE','%'.strtolower($like).'%')
                ->orWhere(strtolower('description'),'LIKE','%'.strtolower($like).'%');
            })
            ->pluck('companies.id');

        return $projets;
    }

    public function getTypeProjects($like)
    {
        $projects = TypeProject::select('projects.id')
            ->where(strtolower('type_projects.name'),'LIKE','%'.strtolower($like).'%')
            ->where('type_projects.status', TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->join('projects','projects.id','=','projects_type_project.projects_id')
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects.id');

        return $projects;
    }

    public function getProjectAddress($like)
    {
        $projects = Addresses::select('addresses.addressable_id')
            ->where('addresses.addressable_type', Projects::class)
            ->where(strtolower('addresses.address'),'LIKE',strtolower($like).'%')
            ->join('projects','projects.id','=','addresses.addressable_id')
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->pluck('addresses.addressable_id');

        return $projects;
    }

    public function getTenders($like)
    {
        //trae los ids de las licitaciones que esta en ultimas versiones publicadas
        $tendesPublish          = $this->getTendersLastVersionPublish();

        //buscar por los tags de la licitacion y retorna los ids de las licitaciones relacionadas
        $tenderTag              = $this->getTenderTags($like, $tendesPublish);
        //buscar por el nombre o descripción de la licitación y retorna los ids de las licitaciones relacionadas
        $tenderNameDescript     = $this->getTenderNameDescript($like, $tendesPublish);
        //buscar por la adenda y retorna los ids de las licitaciones relacionadas
        $tenderAdenda           = $this->getTenderAdenda($like, $tendesPublish);

        $tender_ids             = array_unique(array_merge(json_decode($tenderTag), json_decode($tenderNameDescript), json_decode($tenderAdenda)));

        $tender                 = Tenders::WhereIn('id', $tender_ids)->orderBy('name', 'asc')->get();

        return $this->showAllPaginate($tender);
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

    public function getTenderTags($like, $tendesPublish)
    {
        $tenderTag = Tags::select('tagsable_id')
            ->where('tagsable_type', TendersVersions::class)
            ->whereIn('tagsable_id', $tendesPublish)
            ->where(strtolower('name'),'LIKE','%'.strtolower($like).'%')
            ->distinct('tagsable_id')
            ->pluck('tagsable_id');

        return $tenderTag;
    }

    public function getTenderNameDescript($like, $tendesPublish)
    {
        $tenderNameDescript = Tenders::whereIn('id', $tendesPublish)
            ->where( function($query) use ($like){
                $query->where(strtolower('name'),'LIKE','% '.strtolower($like).'%')
                ->orWhere(strtolower('description'),'LIKE','% '.strtolower($like).'%');
            })
            ->pluck('id');

        return $tenderNameDescript; 
    }

    public function getTenderAdenda($like, $tendesPublish)
    {
        $tenderAdenda = Tenders::whereIn('id', $tendesPublish)
            ->get();

        $tender_last = [];
        foreach ($tenderAdenda as $key => $tender)
        {
            $tender_last[] = $tender->tendersVersionLast()->id;
        };

        $tenders = TendersVersions::whereIn('id', $tender_last)
            ->where(strtolower('adenda'),'LIKE','%'.strtolower($like).'%')
            ->pluck('tenders_id'); 

        return $tenders;
    }

    public function getProducts($like)
    {
        //buscar el producto por el nombre del producto
        $productName    = $this->getProductNameDescription($like);
        //busca los productos relacionados por nombre de tag
        $productTags    = $this->getProductTags($like);
        //busca los productos relacionados por nombre de la marca
        $productBrand   = $this->getProductBrand($like);

        //hace un merge de $productName y $productTags, quita los id repetidos, dejando uno de cada uno
        $products_ids   = array_unique(array_merge(json_decode($productName), json_decode($productTags), json_decode($productBrand)));

        $products       = Products::whereIn('id', $products_ids)->orderBy('name', 'asc')->get();

        return $this->showAllPaginate($products);
    }

    public function getProductNameDescription($like)
    {
        $productName = Products::select('id')
        ->where('status', Products::PRODUCT_PUBLISH)
        ->where( function($query) use ($like){
            $query->where(strtolower('name'),'LIKE','%'.strtolower($like).'%')
            ->orWhere(strtolower('description'),'LIKE','%'.strtolower($like).'%');
        })
        ->pluck('id');  

        return $productName;
    }

    public function getProductTags($like)
    {
        $productTag = Tags::select('tags.tagsable_id')
            ->where('tags.tagsable_type', Products::class)
            ->where(strtolower('tags.name'),'LIKE','%'.strtolower($like).'%')
            ->join('products','products.id','=','tags.tagsable_id')
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->distinct('tags.tagsable_id')
            ->pluck('tags.tagsable_id');

        return $productTag;
    }

    public function getProductBrand($like)
    {
        $productBrands = Brands::select('products.id')
            ->where('brands.status', Brands::BRAND_ENABLED)
            ->where(strtolower('brands.name'),'LIKE','%'.strtolower($like).'%')
            ->join('products','products.brand_id','=','brands.id')
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->pluck('products.id');

        return $productBrands;
    }
}
