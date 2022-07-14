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
        $company_id = $user->companyId();

        $filter = $request->filter;

        $quotes_company = QuotesCompanies::where('company_id', $company_id);

        if(!$user->getAdminUser())
        {
            $quotes_company = $quotes_company->where('user_company_id', '=', $user->id);
        }
  
        $quotes_company = $quotes_company->orderBy('updated_at','desc')
            ->get();

        $transformer = QuotesCompanies::TRANSFORMER_QUOTE_MY_COMPANY;


        // var_dump('pasa por aca');
        // die;

        return $this->showAllPaginateSetTransformer($quotes_company, $transformer);
    }
}
