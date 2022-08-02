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
use App\Mail\SendUpdateQuoteCompany;
use App\Mail\sendRespondTenderCompany;
use App\Mail\sendRecommentTenderCompany;
use App\Mail\SendInvitationQuoteCompany;
use App\Models\TemporalInvitationCompanyQuote;
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

    public function store(Request $request) //envia invitaciones a la COTIZACIÓN.
    {
        $quote_id = $request->quote_id;

        $user = $this->validateUser();

        DB::beginTransaction();
        // 1. optiene información de la COTIZACIÓN.
        $quote  = Quotes::find($quote_id);
        // 2. Registra las compañias nuevas a participar y devuelve un array de IDS de las compañias nuevas.
        $quotesCompaniesNew    = ($request->companies_id) ? $this->createQuoteCompanies($quote, $request->companies_id) : [];
        // 3. cambia el estado de la COTIZACIÓN a 'ABIERTA'.
        $quoteVersion          = $quote->quotesVersionLast();
        $quoteVersion->status  = QuotesVersions::QUOTATION_PUBLISH;
        $quoteVersion->save();
        DB::commit();
        // 4. Envia correos de invitación a las compañias nuevas a participar.
        $this->sendMessageQuoteInvitation($quotesCompaniesNew, $quote);
        // 5. Guarda en una tabla temporal los correos de invitación para luego ser enviados.
        if ($request->companies_email) {
            $this->sendInvitantionExternalCompanies($request->companies_email, $quote);
        }

        return $this->showOne($quote, 201);
    }

    public function store_old(Request $request) //envia invitaciones a la cotización
    {
        $quote_id = $request->quote_id;

        $user = $this->validateUser();

        //Licitación
        $quote                 = Quotes::find($quote_id);

        //compañias que ya estan participando
        $quotesCompaniesOld    = $quote->quoteCompanies;

        //registra las nuevas compañias a la cotización y obtiene una arreglo de las nuevas compañias
        $quotesCompaniesNew    = ($request->companies_id) ? $this->createQuoteCompanies($quote, $request->companies_id) : [];


        //Actualiza el estado de la licitación
        $quoteVersion          = $quote->quotesVersionLast();
        $quoteVersion->status  = QuotesVersions::QUOTATION_PUBLISH;
        $quoteVersion->save();
        DB::commit();

        //Envia los correos e invitaciones a las compañias nuevas a participar
        $this->sendMessageQuoteInvitation($quotesCompaniesNew, $quote);

        //Envia correos y notificaciones a las compañia ya participantes
        $this->sendMessageQuoteVersion($quotesCompaniesOld, $quote);

        //Envia correos de invitación a compañia que no estan registradas en plattaforma
        if ($request->companies_email) {
            $this->sendInvitantionExternalCompanies($request->companies_email, $quote);
        }

        return $this->showOne($quote, 201);
    }

    public function sendInvitantionExternalCompanies($emails, $quote)
    {
        foreach ($emails as $key => $email) {
            if (!($this->emailExistUser($email)) && !($this->invitationTenderExist($email, $quote))) {
                $fields['quote_id']    = $quote->id;
                $fields['email']       = trim($email);

                TemporalInvitationCompanyQuote::create($fields);
            }
        }
    }

    public function invitationTenderExist($email, $tender)
    {
        return TemporalInvitationCompanyQuote::where('quote_id', '=',  $tender->id)
            ->where(strtolower('email'), '=', strtolower($email))
            ->where('send', '=', false)
            ->exists();
    }

    public function emailExistUser($email)
    {
        return User::where('email', '=', $email)
            ->exists();
    }

    public function sendMessageQuoteVersion($quoteCompanies, $tender)
    {
        $notifications = new Notifications();

        foreach ($quoteCompanies as $key => $quoteCompany) {
            if ($quoteCompany->status == QuotesCompanies::STATUS_PARTICIPATING) {

                //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante
                $notifications->registerNotificationQuery(
                    $tender,
                    Notifications::NOTIFICATION_QUOTECOMPANYNEWVERSION,
                    [
                        $quoteCompany->userCompany->id,
                        $quoteCompany->company->user->id
                    ]
                );
                // 2. CORREOS -> Envia los correos a los usuarios por compañia participante
                $this->sendEmailQuoteVersion(
                    [
                        $quoteCompany->userCompany->email,
                        $quoteCompany->company->user->email
                    ],
                    $quoteCompany
                );
            }
        }
    }

    public function sendEmailQuoteVersion($UserEmails, $quoteCompany)
    {
        foreach ($UserEmails as $mail) {
            Mail::to(trim($mail))->send(new SendUpdateQuoteCompany(
                $quoteCompany->quote->name,
                $quoteCompany->quote->quotesVersionLast()->adenda,
                $quoteCompany->company->name
            ));
        }
    }

    public function sendMessageQuoteInvitation($quoteCompanies, $quote)
    {
        $notifications = new Notifications();

        foreach ($quoteCompanies as $key => $quoteCompany) {

            //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante.
            $notifications->registerNotificationQuery(
                $quote,
                Notifications::NOTIFICATION_QUOTEINVITECOMPANIES,
                [$quoteCompany->company->user->id]
            );

            //2. CORREOS -> Envia los correos a los usuarios al usuario administrador de la compañia cotizante.
            $this->sendEmailQuoteInvitation(
                [$quoteCompany->company->user->email],
                $quoteCompany
            );
        }
    }

    public function sendEmailQuoteInvitation($UserEmails, $quoteCompany)
    {
        foreach ($UserEmails as $mail) {
            Mail::to(trim($mail))->send(new SendInvitationQuoteCompany(
                $quoteCompany->quote->name,
                $quoteCompany->quote->quotesVersionLast()->adenda,
                $quoteCompany->company->name
            ));
        }
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

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();
        $status = ($request->status == 'True') ? QuotesCompanies::STATUS_PARTICIPATING : QuotesCompanies::STATUS_REJECTED;

        if ($user->userType() != 'demanda') {
            $companyError = ['quoteCompany' => 'Error, El usuario no puede gestionar la validacion de la compañia hacia la cotización'];
            return $this->errorResponse($companyError, 500);
        }

        $quoteCompany = QuotesCompanies::find($id);
        // Iniciar Transacción
        DB::beginTransaction();

        $quoteCompany->status = $status;

        try {
            $quoteCompany->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $quoteCompanyError = ['quote' => 'Error, no se ha podido gestionar la solicitud de la compañia'];
            return $this->errorResponse($quoteCompanyError, 500);
        }

        DB::commit();

        $email          = $quoteCompany->company->user->email;
        $quote_name     = $quoteCompany->quote->name;
        $company_name   = $quoteCompany->company->name;

        // email pendiente

        //notificaciones pendientes

        return $this->showOne($quoteCompany, 200);
    }
}
