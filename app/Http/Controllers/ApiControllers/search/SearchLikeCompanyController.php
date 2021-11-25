<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Tags;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchLikeCompanyController extends ApiController
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
        $user       = $this->validateUser();
        $type       = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';

        $companies          = $this->getCompanies($type);
        $companiesLikeName  = $this->getLikeNameCompanies($companies, $request->search);
        $companiesLikeTag   = $this->getLikeTagCompanies($companies, $request->search);

        $companiesIds         = array_unique(array_merge(json_decode($companiesLikeName),json_decode($companiesLikeTag)));

        return $this->showAllPaginate($this->getAllCompanies($companiesIds));
    }

    public function getCompanies($type)
    {
        return Company::select('companies.id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', $type)
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
        return Tags::where('tags.name',$search)
            ->where('tags.tagsable_type',Company::class)
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->whereIn('companies.id', $companiesIds)
            ->pluck('companies.id');
    }

    public function getAllCompanies($companiesIds)
    {
        return Company::whereIn('companies.id', $companiesIds)
            ->orderBy('name', 'asc')
            ->get();
    }
}
