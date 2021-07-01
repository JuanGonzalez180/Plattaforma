<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Tenders;
use Illuminate\Console\Command;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
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
        $tenders = Tenders::all();

        foreach($tenders as $tender) {

            $statusValidate = ($tender->tendersVersionLast()->status == TendersVersions::LICITACION_PUBLISH);
            $dateValidate   = ($tender->tendersVersionLast()->date   == Carbon::now()->format('Y-m-d'));
            $hourValidate   = ($tender->tendersVersionLast()->hour   == Carbon::now()->format('G:i'));

            if($statusValidate && $dateValidate && $hourValidate) {
                $tender->tendersVersionLast()->status = TendersVersions::LICITACION_CLOSED;
                $tender->tendersVersionLast()->save();
            };
        }
        
    }
}
