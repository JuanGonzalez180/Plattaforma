<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDeclinedTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Se ha declinado la licitación ";

    protected $tenderName;
    protected $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( string $tenderName, string $companyName)
    {
        $this->tenderName   = $tenderName;
        $this->companyName  = $companyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-declined-tender-company')
        ->subject($this->subject.$this->tenderName)
        ->with([
            'tenderName'        => $this->tenderName,
            'companyName'       => $this->companyName
        ]);
    }
}
