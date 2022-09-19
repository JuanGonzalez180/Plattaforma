<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Quotes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TemporalRecomendation;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendTemporalRecommendationQuote;

class sendRecommendationMessagesQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sendRecommendationMessagesQuotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia los mensajes de recomendación a compañia con etiquetas en comun a la cotización.';

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
        $notificationQuote = TemporalRecomendation::where('modelsable_type', Quotes::class)
            ->where('send', false)
            ->take(10)
            ->get();

        foreach ($notificationQuote as $quoteInvitation) {
            if ($quoteInvitation->quoteExist()) {
                foreach ($quoteInvitation->emails() as $email) {
                    Mail::to($email)->send(new SendTemporalRecommendationQuote(
                        $quoteInvitation->company->name,
                        $quoteInvitation->quote()->id,
                        $quoteInvitation->quote()->company->slug
                    ));
                }
                $quoteInvitation->send = true;
                $quoteInvitation->save();
            } else {
                $quoteInvitation->delete();
            }
        }
    }
}
