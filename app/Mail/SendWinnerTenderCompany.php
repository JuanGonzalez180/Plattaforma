<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendWinnerTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( string $tenderName, string $CompanyName)
    {
        $this->tenderName           = $tenderName;
        $this->CompanyName          = $CompanyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-select-winner-tender-company')
        ->with([
            'tenderName'        => $this->tenderName,
            'CompanyName'       => $this->CompanyName
        ]);
    }
}
