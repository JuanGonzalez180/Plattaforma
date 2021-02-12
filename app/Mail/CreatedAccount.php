<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Creación Cuenta";
    protected $company;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( Company $company, User $user )
    {
        //
        $this->company = $company;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.created-account')
                    ->with([
                        'name' => $this->company->name,
                        'entity' => $this->company->type_entity,
                    ]);
    }
}
