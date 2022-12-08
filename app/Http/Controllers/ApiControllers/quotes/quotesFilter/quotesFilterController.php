<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesFilter;

use JWTAuth;
use App\Models\Quotes;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use App\Http\Controllers\ApiControllers\ApiController;

class quotesFilterController extends ApiController
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
        $filter = null;

        $filter['status']       = $this->quoteStatus();
        $filter['projects']     = $this->getProjects();

        return $filter;
    }

    public function getProjects()
    {
        $quotesCompany = $this->getQuotesCompany();
        
        $projectsId = Quotes::select('project_id')
            ->whereIn('id', $quotesCompany)
            ->pluck('project_id');

        return Projects::select('id','name')
            ->whereIn('id',$projectsId)
            ->orderBy('name','asc')
            ->get();
    }

    public function quoteStatus()
    {
        $status[] = [
            "id" => QuotesVersions::QUOTATION_PUBLISH,
            "name" => QuotesVersions::QUOTATION_PUBLISH,
        ];

        $status[] = [
            "id" => QuotesVersions::QUOTATION_FINISHED,
            "name" => QuotesVersions::QUOTATION_FINISHED,
        ];
        
        return $status;
    }

    public function getQuotesCompany()
    {
        $user       = $this->validateUser();
        $company_id = $user->companyId();

        $quotes_company = QuotesCompanies::select('quotes_id')
            ->where('company_id', $company_id);

        if(!$user->getAdminUser())
        {
            $quotes_company = $quotes_company->where('user_company_id', '=', $user->id);
        }
  
        $quotes_company = $quotes_company
            ->pluck('quotes_id');

        return $quotes_company;
    }
}
