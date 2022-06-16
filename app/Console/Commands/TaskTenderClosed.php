<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Notifications;
use Illuminate\Console\Command;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Traits\UsersCompanyTenders;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\TemporalInvitationCompany;
use App\Mail\tender\tenderClose\sendCloseAdminTender;
use App\Mail\tender\tenderClose\sendCloseTenderCronJobs;

class TaskTenderClosed extends Command
{
    use UsersCompanyTenders;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:tender_closed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra todas las licitaciones';

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
        // *Trae las licitaciones publicadas a cerrar el dia de hoy.
        $tendersVersionLastPublish = $this->getTendersVersionLastPublish();

        $tenders = Tenders::whereIn('id', $tendersVersionLastPublish)->get();

        foreach ($tenders as $tender) {
            // *Valida si la hora de cierre de la licitación es igual a la hora actual.
            $hourValidate   = ($tender->tendersVersionLast()->hour == Carbon::now()->format('H:i'));

            if ($hourValidate) {
                //*Cierra las licitacines.
                $this->tenderVersionUpdate($tender);
                //*Envia las notificaciones
                $this->sendNotificationTenders($tender);
                //*Enviar los correos
                $this->sendEmailsTenders($tender);
            };
        }
    }

    public function tenderVersionUpdate($tender)
    {
        $tender->tendersVersionLast()->status = TendersVersions::LICITACION_CLOSED;
        $tender->tendersVersionLast()->close  = TendersVersions::LICITACION_CLOSED_SYSTEM;
        $tender->tendersVersionLast()->save();
    }

    public function getTendersVersionLastPublish()
    {
        return DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status', TendersVersions::LICITACION_PUBLISH)
            ->where('a.date', Carbon::now()->format('Y-m-d'))
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` != '" . TendersVersions::LICITACION_PUBLISH . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');
    }

    public function sendNotificationTenders($tender)
    {
        $notifications      = new Notifications();
        //*Notifica a los usuarios de las compañias participantes de la licitación.
        $notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED, $tender->TenderParticipatingCompanyIdUsers());
        //*Notifica al administrador y/o encargado de la licitación.
        $notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN, $tender->TenderAdminIdUsers());
    }

    public function sendEmailsTenders($tender)
    {
        // *Correos de las compañias participantes de la licitación.
        $tenderCompaniesEmails = $tender->TenderParticipatingCompanyEmails();

        // *Correos del administrador o encargado de la licitación.
        $tenderAdminEmails     = $tender->TenderAdminEmails();

        foreach ($tenderCompaniesEmails as $companyEmail)
        {
            Mail::to(trim($companyEmail))->send(new sendCloseTenderCronJobs(
                $tender->name,
                $tender->company->name 
            ));
        }

        foreach ($tenderAdminEmails as $adminEmail)
        {
            Mail::to(trim($adminEmail))->send(new sendCloseAdminTender(
                $tender->name,
                $tender->company->name 
            ));
        }
    }
}
