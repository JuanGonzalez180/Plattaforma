<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTemporalRecommendationQuote extends Mailable
{
    use Queueable, SerializesModels;

    protected $companyName;
    protected $quoteId;
    protected $slugCompany;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $quoteId,  string $slugCompany)
    {
        $this->companyName  = $companyName;
        $this->quoteId      = $quoteId;
        $this->slugCompany  = $slugCompany;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-temporal-recommendation-quote')
            ->subject('Hay una cotización nueva que te podría interesar')
            ->with([
                'companyName'   => $this->companyName,
                'quoteId'       => $this->quoteId,
                'slugCompany'   => $this->slugCompany,
            ]);
    }
}
