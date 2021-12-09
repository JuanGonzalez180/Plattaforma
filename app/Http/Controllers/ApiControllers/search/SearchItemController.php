<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Tags;
use App\Models\User;
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


        $search = !isset($request->search) ? null : $request->search;

        $type_entity = !isset($request->type_entity) ? null : $request->type_entity;

        //se empieza a enviar los parametros de busqueda
        switch ($request->type_consult) {
            case 'company':
                $result = $this->getCompanyAll($type_entity, $category_product, $type_project, $category_tender, $search, $date);
                break;
            case 'product':
                $result = $this->getProductAll();
                break;
            case 'catalog':
                $result = $this->getCatalogAll();
                break;
            case 'tender':
                $result = $this->getTenderAll();
                break;
            case 'project':
                $result = $this->getProjectAll($type_entity, $type_project, $category_tender, $search, $date);
                break;
        }

        return $this->showAllPaginate($result);
    }

    public function getCompanyAll($type_entity, $category_product, $type_project, $category_tender, $search, $date)
    {
        $type_user = ($this->validateUser())->userType();

        $companies = $this->getCompanyEnabled();

        if (!is_null($type_entity)) {
            $companies = $this->getCompanyTypeEntity($type_entity);
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
                $companies = $this->getCompaniesTenderCategories($companies, $category_tender, $date);
            }
        }

        if (!is_null($search)) {
            $companies = $this->getCompanySearchNameItem($companies, $search);
        }

        return Company::whereIn('id', $companies)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getProductAll()
    {
        $type_user = ($this->validateUser())->userType();

        $companies = $this->getCompanyEnabled();
    }

    public function getCatalogAll()
    {
    }

    public function getTenderAll()
    {
    }
    public function getProjectAll($type_entity, $type_project, $category_tender, $search, $date)
    {
        $projects = $this->getProjectEnabled();

        if (!is_null($type_entity)) {
            $projects = $this->getProjectsTypeEntity($type_entity);
        }

        if (!is_null($type_project)) {
            $projects = $this->getProjectTypeProjects($projects, $type_project);
        }

        if (!is_null($category_tender)) {
            $projects = $this->getProjectsTenderCategories($projects, $category_tender, $date);
        }

        // if (!is_null($search)) {
        //     // $companies = $this->getCompanySearchNameItem($companies, $search);
        // } else {
        //     $projects = Projects::where('projects.visible','=',Projects::PROJECTS_VISIBLE)
        //         ->join('companies', 'companies.id', '=', 'projects.company_id')
        //         ->whereIn('companies.id',$companies)
        //         ->where('companies.status', Company::COMPANY_APPROVED)
        //         ->pluck('projects.id');
        // }

        return Projects::whereIn('id', $projects)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getCompanySearchNameItem($companies, $search)
    {
        $type_user = ($this->validateUser())->userType();

        $companiesName          = $this->getCompanyName($companies, $search);
        $companiesTags          = $this->getCompanyTags($companies, $search);
        $companiesCatalogs      = $this->getCompanyCatalogs($companies, $search);

        $companiesCategory      = ($type_user == 'demanda')
            ? // si es demanda busca por la categoria del producto
            $this->getCompanyCatProductSearch($companies, $search)
            : //si es oferta busca por la categoria de la licitacion 
            $this->getCompanyCatTenderSearch($companies, $search);

        $companies = array_unique(Arr::collapse([
            $companiesName,
            $companiesTags,
            $companiesCatalogs,
            $companiesCategory
        ]));

        return $companies;
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

    public function getProjectEnabled()
    {
        $user = $this->validateUser();

        $type = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';

        return Projects::where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->join('companies','companies.id','=','projects.company_id')
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.slug', $type)
            ->pluck('projects.id');
    }

    public function getCompanyName($companies, $name)
    {
        return Company::whereIn('id', $companies)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getCompanyTags($companies, $name)
    {
        return Tags::where('tags.tagsable_type', Company::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->whereIn('tags.tagsable_id', $companies)
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->pluck('companies.id');
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

    public function getCompanyTypeEntity($type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $this->getCompanyEnabled())
            ->pluck('companies.id');
    }

    public function getProjectsTypeEntity($type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $this->getCompanyEnabled())
            ->join('projects', 'projects.company_id', '=', 'companies.id')
            ->pluck('projects.id');
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

    public function getTenderVersionDate($tenders, $date)
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
            ->whereIn('tenders.id', $tenders)
            ->pluck('tenders.id');

        return $tendersCompanies;
    }

    public function getCompaniesTenderCategories($companies, $category_tender, $date)
    {
        $tenders = $this->getTenderCategory($companies, $category_tender);

        if (!is_null($date)) {
            $tenders = $this->getTenderVersionDate($tenders, $date);
        }

        return Tenders::whereIn('tenders.id', $tenders)
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->pluck('companies.id');
    }

    public function getProjectsTenderCategories($projects, $category_tender, $date)
    {
        $tenders = $this->getProjectsTendesCategory($projects, $category_tender);

        if (!is_null($date)) {
            $tenders = $this->getTenderVersionDate($tenders, $date);
        }

        return Projects::join('tenders','tenders.project_id','=','projects.id')
            ->whereIn('tenders.id', $tenders)
            ->pluck('projects.id');
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
