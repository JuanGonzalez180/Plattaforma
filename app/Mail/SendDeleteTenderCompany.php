<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDeleteTenderCompany extends Mailable
{
    use Queueable, SerializesModels;

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
        return $this->view('emails.send-delete-tender-company')
            ->with([
                'tender_name'               => $this->tender_name,
                'company_name'              => $this->company_name
            ]);
    }
}
