<?php

namespace App\Mail\QueryWall;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendGlobalMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La compañia ";

    protected $companyName;
    protected $tenderName;
    protected $tenderId;
    protected $slugCompany;
    protected $globalMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $tenderName, string $tenderId, string $slugCompany, string $globalMessage)
    {
        $this->companyName      = $companyName;
        $this->tenderName       = $tenderName;
        $this->tenderId         = $tenderId;
        $this->slugCompany      = $slugCompany;
        $this->globalMessage    = $globalMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.queryWall.send-global-message-user')
            ->subject($this->subject . $this->companyName.' ha hecho un anuncio en la licitación '.$this->tenderName.'.')
            ->with([
                'companyName'       => $this->companyName,
                'tenderName'        => $this->tenderName,
                'tenderId'          => $this->tenderId,
                'slugCompany'       => $this->slugCompany,
                'globalMessage'     => $this->globalMessage,
            ]);
    }
}
