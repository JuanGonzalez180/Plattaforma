<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use App\Models\Tags;
use App\Models\Brands;
use App\Models\Company;
use App\Models\Category;
use App\Models\Products;
use App\Models\Catalogs;
use App\Models\TypesEntity;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemCompanyController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        // Palabra clave de busqueda de la compañia.
        $search         = !isset($request->search) ? null : $request->search;
        // Tipo de compañia
        $type_entity    = ($request->type_entity == 'all') ? null : $request->type_entity;

        // Tipo de usuario
        $type_user = ($this->validateUser())->userType();

        // Todas las compñias habilitadas
        $companies = $this->getCompanyEnabled();


        // Busca por el tipo de entidad
        if (!is_null($type_entity)) {
            $companies = $this->getCompanyTypeEntity($companies, $type_entity);
        }

        if (!is_null($search)) {
            $companies = $this->getCompanySearchNameItem($companies, $search);
        }

        // $companies = Company::whereIn('id', $companies)
        //     ->orderBy('name', 'asc')
        //     ->get();

        return $companies;
        return $this->showAllTransformer($companies);
    }

    public function getCompanySearchNameItem($companies, $search)
    {
        $type_user = ($this->validateUser())->userType();

        $companiesName              = $this->getCompanyName($companies, $search);
        // $companiesDescription       = $this->getCompanyDescription($companies, $search);
        $companiesTags              = $this->getCompanyTags($companies, $search);
        $companiesCatalogs          = $this->getCompanyCatalogs($companies, $search);
        $companiesCatalogsTags      = $this->getCompanyCatalogsTags($companies, $search);
        $companiesBrandProducts     = $this->getCompanyBrandProducts($companies, $search);
        //nombre del producto
        //etiquetas del producto

        $companiesCategory      = ($type_user == 'demanda')
            ? // si es demanda busca por la categoria del producto
            $this->getCompanyCatProductSearch($companies, $search)
            : //si es oferta busca por la categoria de la licitacion 
            $this->getCompanyCatTenderSearch($companies, $search);

        $companies = array_unique(Arr::collapse([
            $companiesName,
            // $companiesDescription,
            $companiesTags,
            $companiesCatalogs,
            $companiesBrandProducts,
            // $companiesCatalogsTags,
            // $companiesCategory
        ]));

        return $companies;
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

    public function getCompanyDescription($companies, $name)
    {
        return Company::whereIn('id', $companies)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
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

        // return $tenders;
        return [];
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

    public function getCompanyTypeEntity($companies, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->whereIn('companies.id', $companies)
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
}
