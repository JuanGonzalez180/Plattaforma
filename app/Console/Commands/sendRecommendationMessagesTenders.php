<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Tenders;
use Illuminate\Console\Command;
use App\Models\TemporalRecomendation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendTemporalRecommendationTender;


class sendRecommendationMessagesTenders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sendRecommendationMessagesTenders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia los mensajes de recomendación a compañia con etiquetas en comun a la licitación.';

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
        $notoficationTenders = TemporalRecomendation::where('modelsable_type', Tenders::class)
            ->where('send', false)
            ->take(20)
            ->get();

        foreach ($notoficationTenders as $tenderInvitation) {
            if ($tenderInvitation->tenderExist()) {
                foreach ($tenderInvitation->emails() as $email)
                {
                    if($this->s_valid_email($email))
                    {
                        Mail::to($email)->send(new SendTemporalRecommendationTender(
                            $tenderInvitation->company->name,
                            $tenderInvitation->tender()->id, 
                            $tenderInvitation->tender()->company->slug 
                        ));
                        $tenderInvitation->send = true;
                        $tenderInvitation->save();
                    }
                }
            } else {
                $tenderInvitation->delete();
            }
        }// return Command::SUCCESS;
    }

    public function is_valid_email($str)
    {
        $matches = null;
        return (1 === preg_match('/^[A-z0-9\\._-]+@[A-z0-9][A-z0-9-]*(\\.[A-z0-9_-]+)*\\.([A-z]{2,6})$/', $str, $matches));
    }
}
