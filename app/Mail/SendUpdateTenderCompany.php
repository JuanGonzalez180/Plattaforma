<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUpdateTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $tenderName;
    protected $tenderVersionName;
    protected $CompanyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( string $tenderName, string $tenderVersionName, string $CompanyName)
    {
        $this->tenderName           = $tenderName;
        $this->tenderVersionName    = $tenderVersionName;
        $this->CompanyName          = $CompanyName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-update-tender-company')
        ->subject('Se ha creado una nueva adenda para la licitación '.$this->tenderName)
        ->with([
            'tenderName'        => $this->tenderName,
            'tenderVersionName' => $this->tenderVersionName,
            'CompanyName'       => $this->CompanyName
        ]);
    }
}
