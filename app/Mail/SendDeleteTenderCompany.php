<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDeleteTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Se ha cerrado y borrado la licitación ";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $tender_name, string $company_name)
    {
        $this->tender_name          = $tender_name;
        $this->company_name         = $company_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.send-delete-tender-company')
            ->subject($this->subject.$this->tender_name)
            ->with([
                'tender_name'               => $this->tender_name,
                'company_name'              => $this->company_name
            ]);
    }
}
