<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use App\Http\Controllers\ApiControllers\ApiController;

class CuotesCompaniesListController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function indexQuotesCompanies( Request $request ) {
        $user       = $this->validateUser();
        // $company_id = $user->companyId();

        $quotes_company_status      = $this->getQuoteCompaniesStatus($user, QuotesCompanies::STATUS_PROCESS);
        $quotes_company_not_status  = $this->getQuoteCompaniesNotStatus($user, QuotesCompanies::STATUS_PROCESS);

        $quotes_company = $quotes_company_status->merge($quotes_company_not_status);

        $transformer = QuotesCompanies::TRANSFORMER_QUOTE_MY_COMPANY;

        return $this->showAllPaginateSetTransformer($quotes_company , $transformer);
    }

    public function getQuoteCompaniesStatus($user, $status)
    {

        $quotes_company = QuotesCompanies::where('company_id', $user->companyId())
            ->where('status', '=', $status);

        return $quotes_company->orderBy('updated_at','desc')
            ->get();
    }

    public function getQuoteCompaniesNotStatus($user, $status)
    {

        $quotes_company = QuotesCompanies::where('company_id', $user->companyId())
            ->where('status', '<>', $status);

        if(!$user->getAdminUser())
            $quotes_company = $quotes_company->where('user_company_id', $user->id);

        return $quotes_company->orderBy('updated_at','desc')
            ->get();
    }
}
