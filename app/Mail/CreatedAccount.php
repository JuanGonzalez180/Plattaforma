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
    protected $entity;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( Company $company, User $user, string $entity )
    {
        //
        $this->company = $company;
        $this->user = $user;
        $this->entity = $entity;
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
                        'entity' => $this->entity,
                    ]);
    }
}
