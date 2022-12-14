<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendUserConvertUserAdminProject extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Haz sido asignado a administrador";

    protected $fullName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.send-user-convert-admin')
        ->subject($this->subject)
        ->with([
            'fullName' => $this->fullName
        ]);
    }
}
