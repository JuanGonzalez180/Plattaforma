<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendParticipateQuoteCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $quoteName;
    protected $CompanyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( string $quoteName, string $CompanyName)
    {
        $this->quoteName            = $quoteName;
        $this->CompanyName          = $CompanyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-participate-quote-company')
        ->with([
            'quoteName'     => $this->quoteName,
            'CompanyName'   => $this->CompanyName
        ]);
    }
}
