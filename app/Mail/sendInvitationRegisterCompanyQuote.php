<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendInvitationRegisterCompanyQuote extends Mailable
{
    use Queueable, SerializesModels;

    protected $quoteName;
    protected $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string  $quoteName, string $companyName)
    {
        $this->quoteName   = $quoteName;
        $this->companyName  = $companyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-invitation-register-company-quote')
            ->subject('La compañia ha sido invitada a la cotización ' . $this->quoteName)
            ->with([
                'quoteName'     => $this->quoteName,
                'companyName'   => $this->companyName,
            ]);
    }
}
