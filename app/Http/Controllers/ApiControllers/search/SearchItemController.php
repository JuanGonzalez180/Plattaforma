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
        }elseif (isset($value_one)) {
            return $value_one;
        }

        return $value_main;
    }

    public function search(Request $request)
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

        if ($type_user == 'demanda') {
            //cuando se busca por compañia
            if ($request->type_consult == 'company') {
                // pero no el tipo de entidad, ni la categoria del producto.
                if (!isset($request->type_entity) && !isset($category_product)) {
                    $result = $this->getCompanyAll(null, null, null, null);
                }
                // ingresa el tipo de entidad, pero no la categoria del producto.
                else if (isset($request->type_entity) && !isset($category_product)) {
                    $result = $this->getCompanyAll($request->type_entity, null, null, null);
                }
                // ingresa el tipo de entidad, ingresa la categoria del producto.
                else if (isset($request->type_entity) && isset($category_product)) {
                    $result = $this->getCompanyAll($request->type_entity, $category_product, null, null);
                }
            }
        } else if ($type_user == 'oferta') {
            //cuando se busca por compañia
            if ($request->type_consult == 'company') {
                // no tiene tipo de entidad, no tiene tipo de proyecto, no tiene categoria de la licitacion
                if (!isset($request->type_entity) && !isset($type_project) && !isset($category_tender)) {
                    $result = $this->getCompanyAll(null, null, null, null);
                }
                //tiene tipo de entidad, no tiene tipo de proyecto, no tiene categoria de la licitacion
                else if (isset($request->type_entity) && !isset($type_project) && !isset($category_tender)) {
                    $result = $this->getCompanyAll($request->type_entity, null, null, null);
                }
                //tiene tipo de entidad, tiene tipo de proyecto, no tiene categoria de la licitacion
                else if (isset($request->type_entity) && isset($type_project) && !isset($category_tender)) {
                    $result = $this->getCompanyAll($request->type_entity, null, $type_project, null);
                }
                //tiene tipo de entidad, tiene tipo de proyecto, tiene categoria de la licitacion
                else if (isset($request->type_entity) && isset($type_project) && isset($category_tender)) {
                    $result = $this->getCompanyAll($request->type_entity, null, $type_project, $category_tender);
                }
            }
        }

        return $result;
    }

    public function getCompanyAll($type_entity, $category_product, $type_project, $category_tender)
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();

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
                $companies = $this->getCompanyCategoryTender($companies, $category_tender);
            }
        }

        return Company::whereIn('id', $companies)
            ->get();
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

    public function getCompanyTypeEntity($type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $this->getCompanyEnabled())
            ->pluck('companies.id');
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

    public function getCompanyCategoryTender($companies, $category_tender)
    {
        $childs = $this->getChildCategory($category_tender);

        return Category::where('categories.status', Category::CATEGORY_PUBLISH)
            ->whereIn('categories.id', $childs)
            ->join('category_tenders','category_tenders.category_id','=','categories.id')
            ->join('tenders','tenders.id','=','category_tenders.tenders_id')
            ->whereIn('tenders.id', $this->getTendersPublish())
            ->join('companies','companies.id','=','tenders.company_id')
            ->whereIn('companies.id',$companies)
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
}
