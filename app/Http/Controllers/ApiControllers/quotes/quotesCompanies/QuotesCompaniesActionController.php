<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;

use JWTAuth;
use App\Models\Quotes;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\quote\quoteClose\sendTenderClose;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesCompaniesActionController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }


    public function SelectedWinner(Request $request)
    {
        $id                = $request->id;
        $quote             = Quotes::find($id);
        $quoteVersionLast  = $quote->quotesVersionLast();

        DB::beginTransaction();

        // $quote->winner      = QuotesCompanies::WINNER_TRUE;
        $quoteVersionLast->status  = QuotesVersions::QUOTATION_FINISHED;

        try {
            $quoteVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse($tenderError, 500);
        }
        //envia los correos a las empresas que participaron en la cotización
        $this->sendEmailQuoteCompanies($quote);
        //envia las notificaciones a las compañias licitantes. incluyendo a la compañia ganadora y a las demas que participarón
        $this->sendNotificationQuoteCompanies($quote);

        return $this->showOne($quoteVersionLast , 200);
    }

    public function sendEmailQuoteCompanies($quote)
    {
        $quote_name     = $quote->name;
        $quote_company  = $quote->company->name;
        $companies_emails   = $quote->QuoteParticipatingCompanyEmails();

        foreach ($companies_emails as $email) {
            Mail::to(trim($email))
                ->send(new sendTenderClose($quote_name, $quote_company));
        }

    }

    public function sendNotificationQuoteCompanies($quote)
    {
        $quote_name         = $quote->name;
        $quote_company      = $quote->company->name;
        $companies_user_id  = $quote->QuoteParticipatingCompanyIdUsers();

        $notifications = new Notifications();
        $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_QUOTE_CLOSED, $companies_user_id);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
