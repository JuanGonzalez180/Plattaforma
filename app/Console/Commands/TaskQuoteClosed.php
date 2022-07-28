<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Quotes;
use App\Models\QuotesVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
                //*Cierra las licitacines.
                $this->quoteVersionUpdate($quote);
                //*Envia las notificaciones
                // $this->sendNotificationTenders($quote);
                //*Enviar los correos
                // $this->sendEmailsTenders($quote);
            };
        }
    }

    public function quoteVersionUpdate($tender)
    {
        $tender->quotesVersionLast()->status = QuotesVersions::QUOTATION_CLOSED;
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
