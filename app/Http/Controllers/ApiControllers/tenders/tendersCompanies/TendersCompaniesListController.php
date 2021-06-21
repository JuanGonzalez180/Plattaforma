<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Transformers\TendersCompaniesTransformer;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersCompaniesListController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function indexTendersCompanies( Request $request ) {

        $user       = $this->validateUser();
        $company_id = $user->companyId();

        $filter = $request->filter;

        $tenders_company = TendersCompanies::where('company_id', $company_id)
            ->orderBy('updated_at','desc')
            ->get();

        $tendersCompaniesTransformer = new TendersCompaniesTransformer();

        $transformer = $tenders_company->first()->transformerTenderMyCompany;

        return $this->showAllPaginateSetTransformer($tenders_company, $transformer);

    }
}
