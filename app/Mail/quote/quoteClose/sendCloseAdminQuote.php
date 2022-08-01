<?php

namespace App\Mail\quote\quoteClose;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendCloseAdminQuote extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La cotización ";

    protected $quoteName;
    protected $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $quoteName, string $companyName)
    {
        $this->quoteName    = $quoteName;
        $this->companyName  = $companyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quote.send-close-admin-quote')
            ->subject($this->subject . $this->quoteName . ' se ha cerrado, procede a evaluar.')
            ->with([
                'quoteName'         => $this->quoteName,
                'companyName'       => $this->companyName
            ]);
    }
}
