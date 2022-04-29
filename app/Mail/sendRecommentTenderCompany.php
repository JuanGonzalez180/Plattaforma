<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendRecommentTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    protected $tenderName;
    protected $companyName;
    protected $slug;
    protected $tenderId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string  $tenderName, string $companyName, string $slug, string $tenderId)
    {
        $this->tenderName   = $tenderName;
        $this->companyName  = $companyName;
        $this->slug         = $slug;
        $this->tenderId     = $tenderId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-recomment-tender-company')
        ->subject('Te puede interesar esta licitación'.$this->tenderName)
        ->with([
            'tenderName'    => $this->tenderName,
            'companyName'   => $this->companyName,
            'slug'          => $this->slug,
            'tenderId'      => $this->tenderId
        ]);
    }
}
