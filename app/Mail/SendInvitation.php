<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Invitación enviada";
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( User $user )
    {
        //
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send-invitation')
                    ->with([
                        'name' => $this->user->username,
                    ]);
    }
}
