<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyQuotes;

use JWTAuth;
use App\Models\Company;
use App\Models\Quotes;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use Illuminate\Support\Facades\DB;
use App\Mail\SendOfferTenderCompany;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Storage;
use App\Transformers\QuotesTransformer;
use App\Mail\SendRetirementTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;
use App\Models\QuotesCompanies;

class CompanyQuotesController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeQuoteCompany   = 'images/quotecompany/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index($slug, Request $request)
    {
        // Validamos TOKEN del usuario
        $user           = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId  = $user->companyId();
        $project_id     = $request->project_id;

        $company = Company::where('slug', $slug)->first();

        if (!$company) {
            $companyError = ['company' => 'Error, no se ha encontrado ninguna compañia'];
            return $this->errorResponse($companyError, 500);
        }

        // Traer Cotizaciones
        $quotes = Quotes::select('quotes.*', 'comp.status AS company_status')
            ->where('quotes.company_id', $company->id)
            ->join('projects', 'projects.id', '=', 'quotes.project_id');

        if ($project_id > 0) {
            $quotes = $quotes->where('projects.id', $project_id);
        };

        $quotes = $quotes->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->leftjoin('quotes_companies AS comp', function ($join) use ($userCompanyId) {
                $join->on('quotes.id', '=', 'comp.quotes_id');
                $join->where('comp.company_id', '=', $userCompanyId);
            })
            ->orderBy('quotes.updated_at', 'desc')
            ->get();

        $company->quotes = $this->getQuoteCompany($company->id);

        foreach ($company->quotes as $key => $quote) {
            $user = $quote->user;
            unset($quote->user);
            $quote->user = $user;

            $version = $quote->quotesVersionLast();

            if ($version) {
                $quote->tags = $version->tags;
            }
            $quote->project;
        }

        return $this->showAllPaginate($company->quotes);
    }

    public function getQuoteCompany($company_id)
    {
        return Quotes::where('company_id', $company_id)
            ->whereIn('id', $this->getQuotesPublish())
            ->get();
    }

    public function getQuotesPublish()
    {
        return DB::table('quotes_versions as a')
            ->select(DB::raw('max(a.created_at), a.quotes_id'))
            ->where('a.status', QuotesVersions::QUOTATION_PUBLISH)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `quotes_versions` as `b` 
                    where `b`.`status` != '" . QuotesVersions::QUOTATION_PUBLISH . "'  
                    and `b`.`quotes_id` = a.quotes_id")
                );
            }), '=', 0)
            ->groupBy('a.quotes_id')
            ->pluck('a.quotes_id');
    }

    public function show($slug, $id)
    {
        $user           = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId  = $user->companyId();
        $quote          = Quotes::where('id', $id)->first();

        // quotes Company
        $company_status = '';
        $quoteCompany = QuotesCompanies::where('quotes_id', $id)
            ->where('company_id', $userCompanyId)
            ->first();
        
        if ($quoteCompany && $quoteCompany->status) {
            $company_status = $quoteCompany->status;
        }

        if (!$id || !$quote) {
            $QuoteError = ['company' => 'Error, no se ha encontrado ninguna cotización'];
            return $this->errorResponse($QuoteError, 500);
        }


        // Traer Cotizaciones
        $user = $quote->user;
        unset($quote->user);
        $quote->user = $user;

        $version = $quote->quotesVersionLastPublish();
        if ($version) {
            $quote->tags = $version->tags;
        }

        $quotesTransformer = new QuotesTransformer();

        foreach ($quote->quotesVersion as $key => $version) {
            if ($version->status == QuotesVersions::QUOTATION_PUBLISH && $slug != $user->companyClass()->slug)
            {
                unset($quote->quotesVersion[$key]);
            }
        }

        if (
            $company_status == QuotesCompanies::STATUS_PARTICIPATING ||
            $company_status == QuotesCompanies::STATUS_PROCESS ||
            $slug == $user->companyClass()->slug
        ) {
            foreach ($quote->quotesVersion as $key => $version) {
                $version->files;
            }

            $quote->company_status = $company_status;
            return $this->showOneData($quotesTransformer->transformDetail($quote), 200);
        }

        // Solamente estos datos
        $quote->quotesVersionLastPublish = $quote->quotesVersionLastPublish();
        // $quote->categories = $quote->categories;
        $quote->company_status = $company_status;

        return $this->showOne($quote, 200);
    }

    public function edit($id)
    {
        $user = $this->validateUser();

        $quote_company = QuotesCompanies::findOrFail($id);
        $quote_company->files;

        $quote_company->quote_value = $quote_company->quote->quotesVersionLast()->price; 

        return $this->showOne($quote_company, 200);
    }

}
