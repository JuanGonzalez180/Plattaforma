<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvitationQuoteCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $quoteName;
    protected $quoteVersionName;
    protected $CompanyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $quoteName, string $quoteVersionName, string $CompanyName)
    {
        $this->quoteName           = $quoteName;
        $this->quoteVersionName    = $quoteVersionName;
        $this->CompanyName         = $CompanyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quote.send-invitation-quote-company')
        ->subject('La compañia ha sido invitada a la cotización '.$this->quoteName)
        ->with([
            'quoteName'         => $this->quoteName,
            'quoteVersionName'  => $this->quoteVersionName,
            'CompanyName'       => $this->CompanyName
        ]);
    }
}
