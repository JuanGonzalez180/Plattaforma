<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTemporalRecommendationTender extends Mailable
{
    use Queueable, SerializesModels;

    protected $companyName;
    protected $tenderId;
    protected $slugCompany;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $tenderId,  string $slugCompany)
    {
        $this->companyName  = $companyName;
        $this->tenderId     = $tenderId;
        $this->slugCompany  = $slugCompany;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-temporal-recommendation-tender')
        ->subject('Hay una licitación nueva que te podría interesar')
        ->with([
            'companyName'   => $this->companyName,
            'tenderId'      => $this->tenderId,
            'slugCompany'   => $this->slugCompany,
        ]);
    }
}
