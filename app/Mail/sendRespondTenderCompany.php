<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\TendersCompanies;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendRespondTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $tenderName;
    protected $CompanyName;
    protected $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( string $tenderName, string $CompanyName, string $status)
    {
        $this->tenderName           = $tenderName;
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
        return $this->view('emails.send-respond-tender-company')
        ->with([
            'tenderName'        => $this->tenderName,
            'CompanyName'       => $this->CompanyName,
            'status'            => $this->status,
        ]);
    }
}
