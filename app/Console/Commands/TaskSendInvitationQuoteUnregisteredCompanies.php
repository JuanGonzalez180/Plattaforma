<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TemporalInvitationCompanyQuote;
use App\Mail\sendInvitationRegisterCompanyQuote;
class TaskSendInvitationQuoteUnregisteredCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:task_send_invitation_quote_unregistered_companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia los correos de una cotización a compañias no registradas a plattaforma';

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
        $email = TemporalInvitationCompanyQuote::where('send',false)->get();

        foreach ($email as $key => $value)
        {
            Mail::to(trim($value->email))->send(new sendInvitationRegisterCompanyQuote(
                $value->quote->name,
                $value->quote->company->name  
            ));

            $value->send = true;
            $value->save();
        }
    }
}
