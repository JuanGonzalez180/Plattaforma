<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;

use JWTAuth;
use App\Models\Tags;
use App\Models\User;
use App\Models\Tenders;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
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

class QuotesCompaniesController extends Controller
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
        $tender_id = $request->tender_id;

        $tender = Tenders::where('id', $tender_id)->first();
        $version = $tender->tendersVersionLastPublish();

        if ($user->userType() != 'demanda' && ($version->status == TendersVersions::LICITACION_CREATED || $version->status == TendersVersions::LICITACION_PUBLISH)) {
            $companyError = ['company' => 'Error, El usuario no puede listar las compañias participantes'];
            return $this->errorResponse($companyError, 500);
        }

        $companies = TendersCompanies::select('tenders_companies.*', 'images.url')
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->leftJoin('images', function ($join) {
                $join->on('images.imageable_id', '=', 'companies.id');
                $join->where('images.imageable_type', '=', Company::class);
            })
            ->where('tenders_companies.tender_id', $tender_id)
            ->get();

        return $this->showAllPaginate($companies);
    }
}
