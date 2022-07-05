<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;

use JWTAuth;
use App\Models\Tags;
use App\Models\User;
use App\Models\Quotes;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use App\Traits\UsersCompanyTenders;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendUpdateTenderCompany;
use App\Mail\sendRespondTenderCompany;
use App\Mail\sendRecommentTenderCompany;
use App\Models\TemporalInvitationCompany;
use App\Mail\SendInvitationTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;

use Illuminate\Support\Facades\Storage;

class QuotesCompaniesController extends ApiController
{
    use UsersCompanyTenders;

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();
        $quote_id = $request->quote_id;

        $quote      = Quotes::find($quote_id);
        $version    = $quote->quotesVersionLastPublish();

        if ($user->userType() != 'demanda' && ($version->status == QuotesVersions::QUOTATION_CREATED || $version->status == QuotesVersions::QUOTATION_PUBLISH)) {
            $companyError = ['company' => 'Error, El usuario no puede listar las compañias participantes'];
            return $this->errorResponse($companyError, 500);
        }

        $companies = QuotesCompanies::select('quotes_companies.*', 'images.url')
            ->join('companies', 'companies.id', '=', 'quotes_companies.company_id')
            ->leftJoin('images', function ($join) {
                $join->on('images.imageable_id', '=', 'companies.id');
                $join->where('images.imageable_type', '=', Company::class);
            })
            ->where('quotes_companies.quotes_id', $quote_id)
            ->get();

        return $this->showAllPaginate($companies);
    }

    public function store(Request $request) //envia invitaciones a la cotización
    {
        $quote_id = $request->quote_id;

        $user = $this->validateUser();

        //Licitación
        $quote                 = Quotes::find($quote_id);

        //compañias que ya estan participando
        $tendersCompaniesOld    = $quote->quoteCompanies;

        //registra las nuevas compañias a la cotización y obtiene una arreglo de las nuevas compañias
        $tendersCompaniesNew    = ($request->companies_id) ? $this->createQuoteCompanies($quote, $request->companies_id) : [];


        //Actualiza el estado de la licitación
        $quoteVersion          = $quote->quotesVersionLast();
        $quoteVersion->status  = QuotesVersions::QUOTATION_PUBLISH;
        $quoteVersion->save();
        DB::commit();


        return $this->showOne($quote, 201);

    }

    public function createQuoteCompanies($quotes, $companies)
    {
        $user = $this->validateUser();
        $quoteCompanies = [];

        foreach ($companies as $company) {
            $userCompanyId = Company::find($company["id"])->user->id;

            $fields['quotes_id']           = $quotes->id;
            $fields['company_id']          = $company["id"];
            $fields['user_id']             = $user->id;
            $fields['user_company_id']     = $userCompanyId;
            $fields['status']              = QuotesCompanies::STATUS_PROCESS;

            $quoteCompanies[] = QuotesCompanies::create($fields);
        }

        return $quoteCompanies;
    }
}
