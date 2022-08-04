<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Quotes;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\quote\quoteClose\sendCloseAdminQuote;
use App\Mail\quote\quoteClose\sendCloseQuoteCronJobs;

class TaskQuoteClosed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:quote_closed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra todas las cotizaciones';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return Command::SUCCESS;
        // *Trae las licitaciones publicadas a cerrar el dia de hoy.
        $quotesVersionLastPublish = $this->getQuotesVersionLastPublish();

        $quotes = Quotes::whereIn('id', $quotesVersionLastPublish)->get();

        foreach ($quotes as $quote) {
            // *Valida si la hora de cierre de la licitación es igual a la hora actual.
            $hourValidate   = ($quote->quotesVersionLast()->hour == Carbon::now()->format('H:i'));

            if ($hourValidate) {
                //*Cierra las cotizaciones.
                $this->quoteVersionUpdate($quote);
                //*Envia las notificaciones
                $this->sendNotificationQuotes($quote);
                //*Enviar los correos
                $this->sendEmailsQuotes($quote);
            };
        }
    }

    public function sendEmailsQuotes($quote)
    {
        // *Correos de las compañias participantes de la licitación.
        $quoteCompaniesEmails = $quote->QuoteParticipatingCompanyEmails();
        // *Correos del administrador o encargado de la licitación.
        $quoteAdminEmails    = $quote->QuoteAdminEmails();

        foreach ($quoteCompaniesEmails as $companyEmail) {
            Mail::to(trim($companyEmail))->send(new sendCloseQuoteCronJobs(
                $quote->name,
                $quote->company->name
            ));
        }
        foreach ($quoteAdminEmails as $adminEmail) {
            Mail::to(trim($adminEmail))->send(new sendCloseAdminQuote(
                $quote->name,
                $quote->company->name
            ));
        }    
    }

    public function sendNotificationQuotes($quote)
    {
        $notifications      = new Notifications();
        //*Notifica a los usuarios de las compañias participantes de la cotización.
        $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED, $quote->QuoteParticipatingCompanyIdUsers());
        //*Notifica al administrador y/o encargado de la cotización.
        $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_ADMIN, $quote->QuoteAdminIdUsers());
    }

    public function quoteVersionUpdate($tender)
    {
        $tender->quotesVersionLast()->status = QuotesVersions::QUOTATION_FINISHED;
        $tender->quotesVersionLast()->close  = QuotesVersions::QUOTATION_CLOSED_SYSTEM;
        $tender->quotesVersionLast()->save();
    }

    public function getQuotesVersionLastPublish()
    {
        return DB::table('quotes_versions as a')
            ->select(DB::raw('max(a.created_at), a.quotes_id'))
            ->where('a.status', QuotesVersions::QUOTATION_PUBLISH)
            ->where('a.date', Carbon::now()->format('Y-m-d'))
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
}
