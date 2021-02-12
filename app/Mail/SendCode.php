<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCode extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Código Generado";
    protected $code;
    protected $minutes;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $code, $minutes, User $user )
    {
        //
        $this->code = $code;
        $this->minutes = $minutes;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-code')
                    ->with([
                        'code' => $this->code,
                        'name' => $this->user->username,
                    ]);
    }
}
