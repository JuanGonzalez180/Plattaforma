<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesActions;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Quotes;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesActionController extends ApiController
{
    //
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function updateStatusClosed($id)
    {
        $quote             = Quotes::find($id);
        $quoteVersionLast  = $quote->quotesVersionLast();

        DB::beginTransaction();

        $quoteVersionLast->status  = QuotesVersions::QUOTATION_CLOSED;
        $quoteVersionLast->date    = Carbon::now()->format('Y-m-d');
        $quoteVersionLast->hour    = Carbon::now()->format('H:i');
        $quoteVersionLast->close   = QuotesVersions::QUOTATION_CLOSED_USER;

        try{
            $quoteVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $quoteVersionError = [ 'quoteVersionLast' => 'Error, no se ha podido gestionar la solicitud de la cotización'];
            return $this->errorResponse( $quoteVersionError, 500 );
        }

        //*Envia las Notificaciones.
        $this->sendNotificationQuotes($quote);
        //*Envia los Correos.
        $this->sendEmailsQuotes($quote);

        return $this->showOne($quoteVersionLast,200);

    }

    public function sendNotificationQuotes($quote)
    {
        // $notifications  = new Notifications();
        // //*Notifica a las compañias participantes de la licitación.
        // $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_BEFORE, $quote->TenderParticipatingCompanyIdUsers());
        // //*Notifica al administrador y/o encargado de la licitación.
        // $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN, $quote->TenderAdminIdUsers());
    }

    public function sendEmailsQuotes($quote)
    {
        // *Correos de las compañias participantes de la licitación.
        // $emails = $quote->TenderParticipatingCompanyEmails();

        // foreach ($emails as $email)
        // {
        //     Mail::to(trim($email))->send(new sendCloseTenderAdmin(
        //         $quote->name,
        //         $quote->company->name 
        //     ));
        // }
    }
}
