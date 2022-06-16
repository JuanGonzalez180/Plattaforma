<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TemporalInvitationCompany;
use App\Mail\sendInvitationRegisterCompanyTender;
class TaskSendInvitationUnregisteredCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:task_send_invitation_unregistered_companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia los correos de una licitacion a compañias no registradas a plattaforma';

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
        $email = TemporalInvitationCompany::where('send',false)->get();
        
        foreach ($email as $key => $value)
        {
            Mail::to(trim($value->email))->send(new sendInvitationRegisterCompanyTender(
                $value->tender->name,
                $value->tender->company->name  
            ));

            $value->send = true;
            $value->save();
        }
    }
}
