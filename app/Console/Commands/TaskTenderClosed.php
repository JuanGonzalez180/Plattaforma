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
use Illuminate\Support\Facades\Storage;

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
        $tendersVersionLastPublish = $this->getTendersVersionLastPublish();

        $tenders = Tenders::whereIn('id', $tendersVersionLastPublish)->get();

        foreach ($tenders as $tender) {
            $hourValidate   = ($tender->tendersVersionLast()->hour == Carbon::now()->format('H:i'));

            if ($hourValidate)
            {
                $tender->tendersVersionLast()->status = TendersVersions::LICITACION_CLOSED;
                $tender->tendersVersionLast()->save();
                //envia las notificaciones
                $this->sendNotificationTenders($tender);
            };
        }
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
        //notifica a las compañias licitantes, tanto para el admin de la compañia y a los integrantes del equipo
        $this->sendNotificationTenderCompanyClose($tender);
        //notifica al encargado de la notificación y al admin de la compañia
        $notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN,[$tender->user_id, $tender->company->user_id] );
    }
    
    public function sendNotificationTenderCompanyClose($tender)
    {
        $companies = Company::whereIn('id',$this->getTendersCompanies($tender))
            ->get();
        
        $notifications      = new Notifications();

        foreach($companies as $company)
        {
            $notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_TENDER_STATUS_CLOSED, $this->getTeamsCompanyUsers($company,'id'));
        }
    }

    public function getTendersCompanies($tender)
    {
        return TendersCompanies::where('tenders_companies.tender_id', $tender->id)
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->where('companies.status','=',Company::COMPANY_APPROVED)
            ->where('tenders_companies.status','=',TendersCompanies::STATUS_PARTICIPATING)
            ->pluck('companies.id')
            ->all(); 
    }

}
