<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
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

        $tenders_company = TendersCompanies::where('company_id', $company_id);

        if(!$user->getAdminUser())
        {
            $tenders_company = $tenders_company->where('user_company_id', '=', $user->id);
        }
  
        $tenders_company = $tenders_company->orderBy('updated_at','desc')
            ->get();

        // $tenders_company_earring = TendersCompanies::where('company_id', $company_id)
        //     ->where('status','=',TendersCompanies::STATUS_EARRING)
        //     ->orderBy('updated_at','desc')
        //     ->get();
            
        // $tenders_company = TendersCompanies::where('company_id', $company_id)
        //     ->where('status','<>',TendersCompanies::STATUS_EARRING)
        //     ->orderBy('updated_at','desc')
        //     ->get();

        // $tenders_company = $tenders_company_earring->merge($tenders_company);

        $transformer = TendersCompanies::TRANSFORMER_TENDER_MY_COMPANY;

        return $this->showAllPaginateSetTransformer($tenders_company, $transformer);

    }
}
