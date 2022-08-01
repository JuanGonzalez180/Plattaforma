<?php

namespace App\Mail\quote\quoteClose;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendTenderClose extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La cotización ";

    protected $quoteName;
    protected $quoteCompany;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $quoteName,string $quoteCompany)
    {
        $this->quoteName        = $quoteName;
        $this->quoteCompany     = $quoteCompany;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quote.send-close-quote')
            ->subject($this->subject . $this->quoteName . ' se ha cerrado, gracias por participar.')
            ->with([
                'quoteName'         => $this->quoteName,
                'quoteCompany'      => $this->quoteCompany,
            ]);
    }
}
