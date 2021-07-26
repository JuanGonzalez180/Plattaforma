<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Tenders;
use Illuminate\Console\Command;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskTenderClosed extends Command
{
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
        $tendersVersionLastPublish = DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status',TendersVersions::LICITACION_PUBLISH)
            ->where('a.date',Carbon::now()->format('Y-m-d'))
            ->where((function($query)
            {
                $query->select(DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where (`b`.`status` = '".TendersVersions::LICITACION_FINISHED."' 
                    or `b`.`status` = '".TendersVersions::LICITACION_CLOSED."') 
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');

        $tenders = Tenders::whereIn('id',$tendersVersionLastPublish)->get();

        foreach($tenders as $tender) {
            $hourValidate   = ($tender->tendersVersionLast()->hour == Carbon::now()->format('H:i'));

            if($hourValidate) {
                $tender->tendersVersionLast()->status = TendersVersions::LICITACION_CLOSED;
                $tender->tendersVersionLast()->save();
            };
        }
        
    }
}
