<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\QuotesCompanies;
use Illuminate\Queue\SerializesModels;

class sendRespondQuoteCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $quoteName;
    protected $CompanyName;
    protected $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $quoteName, string $CompanyName, string $status)
    {
        $this->quoteName           = $quoteName;
        $this->CompanyName          = $CompanyName;
        $this->status               = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Solicitud de licitación " . $this->quoteName . " aprobada";
        if ($this->status != QuotesCompanies::STATUS_PARTICIPATING) {
            $subject = "Solicitud de licitación " . $this->quoteName . " no aprobada";
        }

        return $this->markdown('emails.send-respond-quote-company')
            ->subject($subject)
            ->with([
                'quoteName'         => $this->quoteName,
                'CompanyName'       => $this->CompanyName,
                'status'            => $this->status,
            ]);
    }
}
