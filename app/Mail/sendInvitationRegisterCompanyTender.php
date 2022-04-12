<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendInvitationRegisterCompanyTender extends Mailable
{
    use Queueable, SerializesModels;

    protected $tenderName;
    protected $companyName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string  $tenderName, string $companyName)
    {
        $this->tenderName   = $tenderName;
        $this->companyName  = $companyName;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-invitation-register-company-tender')
        ->subject('La compañia ha sido invitada a la licitación '.$this->tenderName)
        ->with([
            'tenderName'    => $this->tenderName,
            'companyName'   => $this->companyName,
        ]);
    }
}
