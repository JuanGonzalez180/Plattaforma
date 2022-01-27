<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Tags;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchCompanyController extends ApiController
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
        $name = $request->name;

        $tender_id = $request->tender_id;

        $companiesTenders = [];

        if ($tender_id) {
            $companiesTenders = TendersCompanies::where('tender_id', '=', $tender_id)->pluck('company_id');
        }

        $companies                          = $this->getCompanies($companiesTenders);
        $companiesLikeName                  = $this->getLikeNameCompanies($companies, $name);
        $companiesLikeTag                   = $this->getLikeTagCompanies($companies, $name);
        $companieslikeProductCategories     = $this->getlikeProductCategories($companies, $name);

        $companiesIds         = array_unique(array_merge(
            json_decode($companiesLikeName),
            json_decode($companiesLikeTag),
            json_decode($companieslikeProductCategories)
        ));

        return $this->showAllPaginate($this->getAllCompanies($companiesIds));
    }

    public function getCompanies($companiesTenders)
    {
        return Company::select('companies.id')
            ->whereNotIn('companies.id', $companiesTenders)
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', 'oferta')
            ->pluck('companies.id');
    }

    public function getLikeNameCompanies($companiesIds, $search)
    {
        return Company::select('companies.id')
            ->whereIn('companies.id', $companiesIds)
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->pluck('companies.id');
    }

    public function getLikeTagCompanies($companiesIds, $search)
    {
        return Tags::where(strtolower('tags.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->where('tags.tagsable_type', Company::class)
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->whereIn('companies.id', $companiesIds)
            ->pluck('companies.id');
    }

    public function getlikeProductCategories($companiesIds, $search)
    {
        return Category::where(strtolower('categories.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->where('categories.status', Category::CATEGORY_PUBLISH)
            ->join('category_products', 'category_products.category_id', '=', 'categories.id')
            ->join('products', 'products.id', '=', 'category_products.products_id')
            ->where('products.status', '=', Products::PRODUCT_PUBLISH)
            ->whereIn('products.company_id', $companiesIds)
            ->pluck('products.company_id');
    }

    public function getAllCompanies($companiesIds)
    {
        return Company::select('companies.id', 'companies.name', 'companies.slug')
            ->whereIn('companies.id', $companiesIds)
            ->orderBy('name', 'asc')
            ->get();
    }
}
