<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Tags;
use App\Models\Brands;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\Category;
use App\Models\Catalogs;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\Products;
use Illuminate\Support\Arr;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Models\CategoryTenders;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use App\Models\CategoryProducts;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function getAssignValue($value_main, $value_one, $value_two)
    {
        if (isset($value_two)) {
            return $value_two;
        } elseif (isset($value_one)) {
            return $value_one;
        }

        return $value_main;
    }

    public function getAssignDate($date_start, $date_end)
    {
        $date = null;
        if (isset($date_start) || isset($date_end)) {
            $date['date_start'] = !isset($date_start) ? null : $date_start;
            $date['date_end']   = !isset($date_end) ? null : $date_end;
        };

        return $date;
    }

    public function __invoke(Request $request)
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();

        $result = [];

        $category_product = $this->getAssignValue(
            $request->categoryproduct,
            $request->category_product_one,
            $request->category_product_two
        );

        $type_project = $this->getAssignValue(
            $request->typeproject,
            $request->type_project_one,
            $request->type_project_two
        );

        $category_tender = $this->getAssignValue(
            $request->categorytender,
            $request->category_tender_one,
            $request->category_tender_two
        );

        $date = $this->getAssignDate($request->date_start, $request->date_end);


        $search      = !isset($request->search) ? null : $request->search;
        $type_entity = !isset($request->type_entity) ? null : $request->type_entity;
        $status      = !isset($request->status) ? null : $request->status;

        if (!isset($request->type_consult)) {
            return [];
        }

        //se empieza a enviar los parametros de busqueda
        switch ($request->type_consult) {
            case 'companies':
                $result = $this->getCompanyAll($type_entity, $category_product, $type_project, $category_tender, $search, $date);
                break;
            case 'products':
                $result = $this->getProductAll($type_entity, $category_product, $search);
                break;
            case 'catalogs':
                $result = $this->getCatalogAll($type_entity, $search);
                break;
            case 'tenders':
                $result = $this->getTenderAll($status, $type_entity, $type_project, $category_tender, $search, $date);
                break;
            case 'projects':
                $result = $this->getProjectAll($status, $type_entity, $type_project, $category_tender, $search, $date);
                break;
        }

        return $this->showAllPaginate($result);
    }

    public function getCompanyAll($type_entity, $category_product, $type_project, $category_tender, $search, $date)
    {
        $type_user = ($this->validateUser())->userType();

        $companies = $this->getCompanyEnabled();

        if (!is_null($type_entity)) {
            $companies = $this->getCompanyTypeEntity($companies, $type_entity);
        }

        if ($type_user == 'demanda') {
            if (!is_null($category_product)) {
                $companies = $this->getCompanyCatProduct($companies, $category_product);
            }
        } else if ($type_user == 'oferta') {
            if (!is_null($type_project)) {
                $companies = $this->getCompanyTypeProjects($companies, $type_project);
            }
            if (!is_null($category_tender)) {
                $companies = $this->getCompaniesTenderCategories($companies, $category_tender);
            }
            if (!is_null($date)) {
                $companies = $this->getCompaniesTenderDate($companies, $date);
            }
        }

        if (!is_null($search)) {
            $companies = $this->getCompanySearchNameItem($companies, $search);
        }

        return Company::whereIn('id', $companies)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getProductAll($type_entity, $category_product, $search)
    {
        $products = $this->getProductEnabled();

        if (!is_null($type_entity)) {
            $products = $this->getProductsTypeEntity($products, $type_entity);
        }

        if (!is_null($category_product)) {
            $products = $this->getProductsCatProduct($products, $category_product);
        }

        if (!is_null($search)) {
            $products = $this->getProductsSearchNameItem($products, $search);
        }

        return Products::whereIn('id', $products)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getCatalogAll($type_entity, $search)
    {
        $catalogs = $this->getCatalogEnabled();

        if (!is_null($type_entity)) {
            $catalogs = $this->getCatalogTypeEntity($catalogs, $type_entity);
        }

        if (!is_null($search)) {
            $catalogs = $this->getCatalogsSearchNameItem($catalogs, $search);
        }

        return Catalogs::whereIn('id', $catalogs)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getTenderAll($status, $type_entity, $type_project, $category_tender, $search, $date)
    {
        $tenders = $this->getTenderEnabled();

        if (!is_null($status)) {
            $tenders = $this->getTendersStatus($tenders, $status);
        }

        if (!is_null($type_entity)) {
            $tenders = $this->getTendersTypeEntity($tenders, $type_entity);
        }

        if (!is_null($type_project)) {
            $tenders = $this->getTenderTypeProjects($tenders, $type_project);
        }

        if (!is_null($category_tender)) {
            $tenders = $this->getTenderCategories($tenders, $category_tender);
        }

        if (!is_null($date)) {
            $tenders = $this->getTenderVersionPublishDate($tenders, $date);
        }

        if (!is_null($search)) {
            $tenders = $this->getTendersSearchNameItem($tenders, $search);
        }

        return Tenders::whereIn('id', $tenders)
            ->orderBy('name', 'asc')
            ->get();
    }
    public function getProjectAll($status, $type_entity, $type_project, $category_tender, $search, $date)
    {
        $projects = $this->getProjectEnabled();

        if (!is_null($status)) {
            $projects = $this->getProjectsStatus($projects, $status);
        }

        if (!is_null($type_entity)) {
            $projects = $this->getProjectsTypeEntity($projects, $type_entity);
        }

        if (!is_null($type_project)) {
            $projects = $this->getProjectTypeProjects($projects, $type_project);
        }

        if (!is_null($category_tender)) {
            $projects = $this->getProjectsTenderCategories($projects, $category_tender);
        }

        if (!is_null($date)) {
            $projects = $this->getProjectsTenderDate($projects, $date);
        }

        if (!is_null($search)) {
            $projects = $this->getProjectsSearchNameItem($projects, $search);
        }

        return Projects::whereIn('id', $projects)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getProductsSearchNameItem($products, $search)
    {
        //busca por el producto por el nombre de la compañia
        $productCompanyName     = $this->getProductCompanyName($products, $search);
        //busca por el nombre del producto
        $productName            = $this->getProductName($products, $search);
        //busca por el codigo del producto
        $productCode            = $this->getProductCode($products, $search);
        //busca por el nombre de las etiquetas del producto
        $productTags            = $this->getProductTags($products, $search);
        //buscar por la marca del producto
        $productBrands          = $this->getProductBrand($products, $search);

        $products = array_unique(Arr::collapse([
            $productCompanyName,
            $productName,
            $productCode,
            $productTags,
            $productBrands
        ]));

        return $products;
    }

    public function getCatalogsSearchNameItem($catalogs, $search)
    {
        $catalogName            = $this->getCatalogName($catalogs, $search);
        $catalogCompanyName     = $this->getCatalogCompanyName($catalogs, $search);
        $catalogCompanyTag      = $this->getCatalogTag($catalogs, $search);

        $catalogs = array_unique(Arr::collapse([
            $catalogName,
            $catalogCompanyName,
            $catalogCompanyTag
        ]));

        return $catalogs;
    }

    public function getCatalogTag($catalogs, $search)
    {
        return Tags::where('tags.tagsable_type', Catalogs::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->whereIn('tags.tagsable_id', $catalogs)
            ->join('catalogs', 'catalogs.id', '=', 'tags.tagsable_id')
            ->pluck('catalogs.id');
    }

    public function getCompanySearchNameItem($companies, $search)
    {
        $type_user = ($this->validateUser())->userType();

        $companiesName              = $this->getCompanyName($companies, $search);
        //$companiesDescription       = $this->getCompanyDescription($companies, $search);
        $companiesTags              = $this->getCompanyTags($companies, $search);
        $companiesCatalogs          = $this->getCompanyCatalogs($companies, $search);
        $companiesCatalogsTags      = $this->getCompanyCatalogsTags($companies, $search);
        $companiesBrandProducts     = $this->getCompanyBrandProducts($companies, $search);


        $companiesCategory      = ($type_user == 'demanda')
            ? // si es demanda busca por la categoria del producto
            $this->getCompanyCatProductSearch($companies, $search)
            : //si es oferta busca por la categoria de la licitacion 
            $this->getCompanyCatTenderSearch($companies, $search);

        $companies = array_unique(Arr::collapse([
            $companiesName,
            //$companiesDescription,
            $companiesTags,
            $companiesCatalogs,
            $companiesBrandProducts,
            $companiesCatalogsTags,
            $companiesCategory
        ]));

        return $companies;
    }

    public function getProjectsSearchNameItem($projects, $search)
    {
        $projectName                = $this->getProjectsName($projects, $search);
        $projectDescription         = $this->getProjectsDescription($projects, $search);
        $projectCompaniesTags       = $this->getProjectsCompanyTags($projects, $search);
        $projectCompaniesName       = $this->getProjectsCompanyName($projects, $search);

        $projects = array_unique(Arr::collapse([
            $projectName,
            $projectDescription,
            $projectCompaniesTags,
            $projectCompaniesName
        ]));

        return $projects;
    }

    public function getTendersSearchNameItem($tenders, $search)
    {
        //nombre de licitacion
        $tendersName                = $this->getTendersName($tenders, $search);
        //descripcion de la licitacion
        $tendersDescription         = $this->getTendersDescription($tenders, $search);
        //nombre la compañia de la licitacion
        $tendersCompanyName         = $this->getTenderCompanyName($tenders, $search);
        //nombre de proyecto de la licitacion
        $tendersProjectName         = $this->getTenderProjectName($tenders, $search);
        //tags de la licitacion
        $tenderVersionTags          = $this->getTenderVersionTags($tenders, $search);

        $tenders = array_unique(Arr::collapse([
            $tendersName,
            $tendersDescription,
            $tendersCompanyName,
            $tendersProjectName,
            $tenderVersionTags
        ]));

        return $tenders;
    }

    public function getCompanyEnabled()
    {
        $user = $this->validateUser();

        $type = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';

        return Company::where('companies.status', Company::COMPANY_APPROVED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.slug', $type)
            ->pluck('companies.id');
    }

    public function getProductEnabled()
    {
        return Products::where('products.status', Products::PRODUCT_PUBLISH)
            ->pluck('products.id');
    }

    public function getCatalogEnabled()
    {
        // return Company::where('companies.status', Company::COMPANY_APPROVED)
        //     ->join('catalogs','catalogs.company_id','=','companies.id')
        //     ->where('catalogs.status', Catalogs::CATALOG_PUBLISH)
        //     ->join('files', 'files.filesable_id', '=', 'catalogs.id')
        //     ->where('files.filesable_type', Catalogs::class)
        //     ->pluck('catalogs.id');
        return Company::join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->where('catalogs.status', Catalogs::CATALOG_PUBLISH)
            ->join('files', 'files.filesable_id', '=', 'catalogs.id')
            ->where('files.filesable_type', Catalogs::class)
            ->pluck('catalogs.id');
    }

    public function getTenderEnabled()
    {
        $tenderVersionEnabled = $this->getTendersPublishVersion();

        return TendersVersions::whereIn('tenders_versions.id', $tenderVersionEnabled)
            ->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            // ->where('companies.status','=',Company::COMPANY_APPROVED)
            ->pluck('tenders.id');
    }

    public function getProjectEnabled()
    {
        $user = $this->validateUser();

        $type = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';

        return Projects::where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.slug', $type)
            ->pluck('projects.id');
    }

    public function getProductCompanyName($products, $name)
    {
        return Company::where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('products', 'products.company_id', '=', 'companies.id')
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->whereIn('products.id', $products)
            ->pluck('products.id');
    }

    public function getCatalogName($catalogs, $name)
    {
        return Catalogs::whereIn('catalogs.id', $catalogs)
            ->where(strtolower('catalogs.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('catalogs.id');
    }

    public function getCatalogCompanyName($catalogs, $name)
    {
        return Company::where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            // ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->whereIn('catalogs.id', $catalogs)
            ->pluck('catalogs.id');
    }

    public function getProductName($products, $name)
    {
        return Products::whereIn('products.id', $products)
            ->where(strtolower('products.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('products.id');
    }

    public function getProductCode($products, $name)
    {
        return Products::whereIn('products.id', $products)
            ->where(strtolower('products.code'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('products.id');
    }

    public function getProductBrand($products, $name)
    {
        return Brands::where(strtolower('brands.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->join('products', 'products.brand_id', '=', 'brands.id')
            ->whereIn('products.id', $products)
            ->pluck('products.id');
    }

    public function getProductTags($products, $name)
    {
        return Tags::where('tags.tagsable_type', Products::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->whereIn('tags.tagsable_id', $products)
            ->join('products', 'products.id', '=', 'tags.tagsable_id')
            ->pluck('products.id');
    }

    public function getCompanyName($companies, $name)
    {
        return Company::whereIn('id', $companies)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getCompanyDescription($companies, $name)
    {
        return Company::whereIn('id', $companies)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getProjectsName($projects, $name)
    {
        return Projects::whereIn('id', $projects)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('id');
    }

    public function getTendersName($tenders, $name)
    {
        return Tenders::whereIn('id', $tenders)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getTendersDescription($tenders, $name)
    {
        return Tenders::whereIn('id', $tenders)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getTenderCompanyName($tenders, $name)
    {
        return Tenders::whereIn('tenders.id', $tenders)
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            // ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->pluck('tenders.id');
    }

    public function getTenderProjectName($tenders, $name)
    {
        return Tenders::whereIn('tenders.id', $tenders)
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->where(strtolower('projects.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('tenders.id');
    }

    public function getTenderVersionTags($tenders, $name)
    {
        return Tenders::whereIn('tenders.id', $tenders)
            ->join('tenders_versions', 'tenders_versions.tenders_id', '=', 'tenders.id')
            ->whereIn('tenders_versions.id', $this->getTendersPublishVersion())
            ->join('tags', 'tags.tagsable_id', '=', 'tenders_versions.id')
            ->where('tags.tagsable_type', '=', 'App\Models\TendersVersions')
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('tenders.id');
    }

    public function getProjectsDescription($projects, $name)
    {
        return Projects::whereIn('id', $projects)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('id');
    }

    public function getProjectsCompanyName($projects, $name)
    {
        return Projects::whereIn('projects.id', $projects)
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->pluck('projects.id');
    }

    public function getCompanyTags($companies, $name)
    {
        return Tags::where('tags.tagsable_type', Company::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->whereIn('tags.tagsable_id', $companies)
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->pluck('companies.id');
    }

    public function getProjectsCompanyTags($projects, $name)
    {
        return Tags::where('tags.tagsable_type', Company::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('projects', 'projects.company_id', '=', 'companies.id')
            ->whereIn('projects.id', $projects)
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('projects.id');
    }

    public function getCompanyCatalogs($companies, $name)
    {
        return Catalogs::whereIn('catalogs.company_id', $companies)
            ->where(strtolower('catalogs.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('catalogs.status', Catalogs::CATALOG_PUBLISH)
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->pluck('companies.id');
    }

    public function getCompanyCatalogsTags($companies, $name)
    {
        return Tags::where('tags.tagsable_type', Catalogs::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->join('catalogs', 'catalogs.id', '=', 'tags.tagsable_id')
            ->whereIn('catalogs.company_id', $companies)
            ->where('catalogs.status', Catalogs::CATALOG_PUBLISH)
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->pluck('companies.id');
    }

    public function getCatalogTypeEntity($catalogs, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            // ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->whereIn('catalogs.id', $catalogs)
            ->pluck('catalogs.id');
    }

    public function getProductsTypeEntity($products, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('products', 'products.company_id', '=', 'companies.id')
            ->whereIn('products.id', $products)
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->pluck('products.id');
    }

    public function getCompanyTypeEntity($companies, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');
    }

    public function getProjectsStatus($projects, $status)
    {
        if ($status == Projects::TECHNICAL_SPECIFICATIONS) {
            $status = Projects::TECHNICAL_SPECIFICATIONS;
        } else if ($status == Projects::IN_CONSTRUCTION) {
            $status = Projects::IN_CONSTRUCTION;
        }

        return Projects::whereIn('id', $projects)
            ->where('projects.status', '=', $status)
            ->pluck('id');
    }

    public function getTendersStatus($tenders, $status)
    {
        if ($status == Projects::TECHNICAL_SPECIFICATIONS) {
            $status = Projects::TECHNICAL_SPECIFICATIONS;
        } else if ($status == Projects::IN_CONSTRUCTION) {
            $status = Projects::IN_CONSTRUCTION;
        }

        return Projects::where('projects.status', '=', $status)
            ->join('tenders', 'tenders.project_id', '=', 'projects.id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('tenders.id');
    }

    public function getProjectsTypeEntity($projects, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $this->getCompanyEnabled())
            ->join('projects', 'projects.company_id', '=', 'companies.id')
            ->whereIn('projects.id', $projects)
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('projects.id');
    }

    public function getTendersTypeEntity($tenders, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $this->getCompanyEnabled())
            ->join('tenders', 'tenders.company_id', '=', 'companies.id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('tenders.id');
    }

    public function getTypeProjectToProjectIds($type_project_id)
    {
        $childs = [];
        if ($type_project_id == 'all') {
            $typesprojects = $this->getProjectItemList(null, null, null);
            $idsTypeProjects = [];
            foreach ($typesprojects as $key => $parent) {
                $childs = array_merge($childs, $this->getProjectIdChildList($parent[0]['id']));
            }
        } else {
            $childs = $this->getProjectIdChildList($type_project_id);
        }

        $projectChildIds = array_column($childs, 'id');

        $type_project_ids = TypeProject::select('projects_type_project.projects_id')
            ->whereIn('type_projects.id', $projectChildIds)
            ->where('type_projects.status', TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects_type_project.projects_id');

        return $type_project_ids;
    }

    public function getProjectIdChildList($id)
    {
        $childs = DB::select('call get_child_type_project("' . $id . '")');

        foreach ($childs as $key => $child) {
            if ($id > $child->id)
                unset($childs[$key]);
        };

        return json_decode(json_encode($childs), true);
    }

    public function getCompanyCatProduct($companiesTypeEntity, $category_product)
    {
        $childs = $this->getChildCategory($category_product);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_products', 'category_products.category_id', '=', 'categories.id')
            ->join('products', 'category_products.products_id', '=', 'products.id')
            ->where('products.status', '=', Products::PRODUCT_PUBLISH)
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->whereIn('companies.id', $companiesTypeEntity)
            ->pluck('companies.id');
    }

    public function getProductsCatProduct($products, $category_product)
    {
        $childs = $this->getChildCategory($category_product);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_products', 'category_products.category_id', '=', 'categories.id')
            ->join('products', 'category_products.products_id', '=', 'products.id')
            ->whereIn('products.id', $products)
            ->where('products.status', '=', Products::PRODUCT_PUBLISH)
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->pluck('products.id');
    }

    public function getChildCategory($category_product)
    {
        $categories = DB::select('call get_child_type_categoty("' . $category_product . '")');

        $childs = [];
        foreach ($categories as $value) {
            if (!is_null($value->parent_id)) {
                $childs[] = $value->id;
            }
        }

        return $childs;
    }

    public function getCompanyTypeProjects($companies, $type_project)
    {
        $childs = $this->getChildTypeProject($type_project);

        return TypeProject::whereIn('type_projects.id', $childs)
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->join('projects', 'projects.id', '=', 'projects_type_project.projects_id')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');
    }

    public function getProjectTypeProjects($projects, $type_project)
    {
        $childs = $this->getChildTypeProject($type_project);

        return TypeProject::whereIn('type_projects.id', $childs)
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->join('projects', 'projects.id', '=', 'projects_type_project.projects_id')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->whereIn('projects.id', $projects)
            ->pluck('projects.id');
    }

    public function getTenderTypeProjects($tenders, $type_project)
    {
        $childs = $this->getChildTypeProject($type_project);

        return TypeProject::whereIn('type_projects.id', $childs)
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->join('projects', 'projects.id', '=', 'projects_type_project.projects_id')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->join('tenders', 'tenders.project_id', '=', 'projects.id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('tenders.id');
    }

    public function getProjectStatus($projects, $type_project)
    {
        $childs = $this->getChildTypeProject($type_project);

        return TypeProject::whereIn('type_projects.id', $childs)
            ->join('projects_type_project', 'projects_type_project.type_project_id', '=', 'type_projects.id')
            ->join('projects', 'projects.id', '=', 'projects_type_project.projects_id')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->whereIn('projects.id', $projects)
            ->pluck('projects.id');
    }

    public function getTenderCategory($companies, $category_tender)
    {
        $childs = $this->getChildCategory($category_tender);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_tenders', 'category_tenders.category_id', '=', 'categories.id')
            ->join('tenders', 'tenders.id', '=', 'category_tenders.tenders_id')
            ->whereIn('tenders.id', $this->getTendersPublish())
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->whereIn('companies.id', $companies)
            ->pluck('tenders.id');
    }

    public function getProjectsTendesCategory($projects, $category_tender)
    {
        $childs = $this->getChildCategory($category_tender);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_tenders', 'category_tenders.category_id', '=', 'categories.id')
            ->join('tenders', 'tenders.id', '=', 'category_tenders.tenders_id')
            ->whereIn('tenders.id', $this->getTendersPublish())
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->whereIn('projects.id', $projects)
            ->pluck('tenders.id');
    }

    public function getCompanyBrandProducts($companies, $search)
    {
        return Brands::where(strtolower('brands.name'), 'LIKE', '%' . strtolower($search) . '%')
            // ->where('brans.status', '=', Company::BRAND_ENABLED)
            ->join('products', 'products.brand_id', '=', 'brands.id')
            ->where('products.status', '=', Products::PRODUCT_PUBLISH)
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');
    }

    public function getCompanyCatProductSearch($companies, $search)
    {
        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->where(strtolower('categories.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->join('category_products', 'category_products.category_id', '=', 'categories.id')
            ->join('products', 'category_products.products_id', '=', 'products.id')
            ->where('products.status', '=', Products::PRODUCT_PUBLISH)
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');
    }

    public function getCompanyCatTenderSearch($companies, $search)
    {
        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->where(strtolower('categories.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->join('category_tenders', 'category_tenders.category_id', '=', 'categories.id')
            ->join('tenders', 'tenders.id', '=', 'category_tenders.tenders_id')
            ->whereIn('tenders.id', $this->getTendersPublish())
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');
    }

    public function getChildTypeProject($type_project)
    {
        $typesProjects = DB::select('call get_child_type_project("' . $type_project . '")');

        $childs = [];
        foreach ($typesProjects as $value) {
            if (!is_null($value->parent_id)) {
                $childs[] = $value->id;
            }
        }

        return $childs;
    }

    public function getTendersPublish()
    {
        $tenders = DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status', TendersVersions::LICITACION_PUBLISH)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` != '" . TendersVersions::LICITACION_PUBLISH . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');

        return $tenders;
    }

    public function getCompaniesTenderDate($companies, $date)
    {
        $date_start = (!is_null($date['date_start'])) ? $date['date_start'] : null;
        $date_end   = (!is_null($date['date_end'])) ? $date['date_end'] : null;

        $tendersCompanies = TendersVersions::whereIn('tenders_versions.id', $this->getTendersPublishVersion());

        if (!is_null($date_start) && is_null($date_end)) {
            $tendersCompanies = $tendersCompanies->whereDate('tenders_versions.date', '>=', $date_start);
        } else if (!is_null($date_start) && !is_null($date_end)) {
            $tendersCompanies = $tendersCompanies->whereBetween('tenders_versions.date', [$date_start, $date_end]);
        } else if (is_null($date_start) && !is_null($date_end)) {
            $tendersCompanies = $tendersCompanies->whereDate('tenders_versions.date', '<=', $date_end);
        }


        $tendersCompanies = $tendersCompanies->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->whereIn('companies.id', $companies)
            ->pluck('companies.id');

        return $tendersCompanies;
    }

    public function getProjectsTenderDate($projects_id, $date)
    {
        $date_start = (!is_null($date['date_start'])) ? $date['date_start'] : null;
        $date_end   = (!is_null($date['date_end'])) ? $date['date_end'] : null;

        $projects = TendersVersions::whereIn('tenders_versions.id', $this->getTendersPublishVersion());

        if (!is_null($date_start) && is_null($date_end)) {
            $projects = $projects->whereDate('tenders_versions.date', '>=', $date_start);
        } else if (!is_null($date_start) && !is_null($date_end)) {
            $projects = $projects->whereBetween('tenders_versions.date', [$date_start, $date_end]);
        } else if (is_null($date_start) && !is_null($date_end)) {
            $projects = $projects->whereDate('tenders_versions.date', '<=', $date_end);
        }

        $projects = $projects->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('projects', 'projects.company_id', '=', 'companies.id')
            ->whereIn('projects.id', $projects_id)
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->pluck('projects.id');

        return $projects;
    }

    public function getTenderVersionPublishDate($tenders, $date)
    {
        $date_start = (!is_null($date['date_start'])) ? $date['date_start'] : null;
        $date_end   = (!is_null($date['date_end'])) ? $date['date_end'] : null;

        $tenders_date = Tenders::whereIn('tenders.id', $tenders)
            ->join('tenders_versions', 'tenders_versions.tenders_id', '=', 'tenders.id')
            ->whereIn('tenders_versions.id', $this->getTendersPublishVersion());

        if (!is_null($date_start) && is_null($date_end)) {
            $tenders_date = $tenders_date->whereDate('tenders_versions.date', '>=', $date_start);
        } else if (!is_null($date_start) && !is_null($date_end)) {
            $tenders_date = $tenders_date->whereBetween('tenders_versions.date', [$date_start, $date_end]);
        } else if (is_null($date_start) && !is_null($date_end)) {
            $tenders_date = $tenders_date->whereDate('tenders_versions.date', '<=', $date_end);
        }

        $tenders_date = $tenders_date->pluck('tenders.id');

        return $tenders_date;
    }

    public function getCompaniesTenderCategories($companies, $category_tender)
    {
        $tenders = $this->getTenderCategory($companies, $category_tender);

        return Tenders::whereIn('tenders.id', $tenders)
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->pluck('companies.id');
    }

    public function getProjectsTenderCategories($projects, $category_tender)
    {
        $tenders = $this->getProjectsTendesCategory($projects, $category_tender);

        return Projects::where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->join('tenders', 'tenders.project_id', '=', 'projects.id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('projects.id');
    }

    public function getTenderCategories($tenders, $category_tender)
    {
        $childs = $this->getChildCategory($category_tender);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_tenders', 'category_tenders.category_id', '=', 'categories.id')
            ->join('tenders', 'tenders.id', '=', 'category_tenders.tenders_id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('tenders.id');
    }

    public function getTendersPublishVersion()
    {

        $tenders = DB::table('tenders_versions as a')
            ->select(
                DB::raw('max(a.created_at), a.tenders_id'),
                DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
                where `c`.`status` = '" . TendersVersions::LICITACION_PUBLISH . "'  
                and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
            )
            ->where('a.status', TendersVersions::LICITACION_PUBLISH)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` != '" . TendersVersions::LICITACION_PUBLISH . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('version_id');

        return $tenders;
    }
}
