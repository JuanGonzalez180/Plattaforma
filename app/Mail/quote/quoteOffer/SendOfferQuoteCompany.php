<?php

namespace App\Mail\quote\quoteOffer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOfferQuoteCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $quoteOfferCompanyName;
    protected $price;
    protected $quoteName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $quoteCompanyName, string $quoteOfferCompanyName, string $price, string $quoteName)
    {
        $this->quoteCompanyName        = $quoteCompanyName;
        $this->quoteOfferCompanyName   = $quoteOfferCompanyName;
        $this->price                   = $price;
        $this->quoteName               = $quoteName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quote.send-offer-quote-company')
        ->subject('La compañia '.$this->quoteCompanyName.' ha ofertado')
        ->with([
            'quoteCompanyName'          => $this->quoteCompanyName,
            'quoteOfferCompanyName'     => $this->quoteOfferCompanyName,
            'price'                     => $this->price,
            'quoteName'                 => $this->quoteName
        ]);
    }
}
