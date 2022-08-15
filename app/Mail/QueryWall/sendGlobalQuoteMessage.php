<?php

namespace App\Mail\QueryWall;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendGlobalQuoteMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La compañia ";

    protected $companyName;
    protected $quoteName;
    protected $quoteId;
    protected $slugCompany;
    protected $globalMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $quoteName, string $quoteId, string $slugCompany, string $globalMessage)
    {
        $this->companyName      = $companyName;
        $this->quoteName        = $quoteName;
        $this->quoteId          = $quoteId;
        $this->slugCompany      = $slugCompany;
        $this->globalMessage    = $globalMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.queryWall.send-global-quote-message-user')
            ->subject($this->subject . $this->companyName . ' ha hecho un anuncio en la cotización ' . $this->quoteName . '.')
            ->with([
                'companyName'       => $this->companyName,
                'quoteName'         => $this->quoteName,
                'quoteId'           => $this->quoteId,
                'slugCompany'       => $this->slugCompany,
                'globalMessage'     => $this->globalMessage,
            ]);
    }
}
