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
        
        $tender_company_status      = $this->getTenderCompaniesStatus($user, TendersCompanies::STATUS_PROCESS);
        $tender_company_not_status  = $this->getTenderCompaniesNotStatus($user, TendersCompanies::STATUS_PROCESS);

        $tender_company = $tender_company_status->merge($tender_company_not_status);


        $transformer = TendersCompanies::TRANSFORMER_TENDER_MY_COMPANY;

        return $this->showAllPaginateSetTransformer($tender_company, $transformer);

    }

    public function getTenderCompaniesStatus($user, $status)
    {

        $tenders_company = TendersCompanies::where('company_id', $user->companyId())
            ->where('status', '=', $status);

        return $tenders_company->orderBy('updated_at','desc')
            ->get();
    }

    public function getTenderCompaniesNotStatus($user, $status)
    {

        $tenders_company = TendersCompanies::where('company_id', $user->companyId())
            ->where('status', '<>', $status);

        if(!$user->getAdminUser())
            $tenders_company = $tenders_company->where('user_company_id', $user->id);

        return $tenders_company->orderBy('updated_at','desc')
            ->get();
    }
}
