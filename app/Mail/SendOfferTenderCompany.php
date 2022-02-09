<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOfferTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $tenderOfferCompanyName;
    protected $price;
    protected $TenderName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $tenderCompanyName, string $tenderOfferCompanyName, string $price, string $TenderName)
    {
        $this->tenderCompanyName        = $tenderCompanyName;
        $this->tenderOfferCompanyName   = $tenderOfferCompanyName;
        $this->price                    = $price;
        $this->TenderName               = $TenderName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-offer-tender-company')
        ->subject('La compañia '.$this->tenderCompanyName.' ha ofertado')
        ->with([
            'tenderCompanyName'         => $this->tenderCompanyName,
            'tenderOfferCompanyName'    => $this->tenderOfferCompanyName,
            'price'                     => $this->price,
            'TenderName'                => $this->TenderName
        ]);
    }
}
