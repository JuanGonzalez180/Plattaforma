<?php

namespace App\Mail\tender\tenderClose;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendCloseTenderAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "El administrador ha cerrado la licitación ";

    protected $tenderName;
    protected $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $tenderName, string $companyName)
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
        // return $this->view('emails.send-declined-tender-company')
        return $this->view('emails.send-close-tender-admin-company')
            ->subject($this->subject . $this->tenderName.' antes de la fecha prevista.')
            ->with([
                'tenderName'        => $this->tenderName,
                'companyName'       => $this->companyName
            ]);
    }
}
