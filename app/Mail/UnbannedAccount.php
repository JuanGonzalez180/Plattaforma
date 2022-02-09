<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnbannedAccount extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject = "Cuenta Desbloqueada";
    protected $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.account-unbanned')
            ->subject($this->subject)
            ->with([
                'name' => $this->user->username,
            ]);
    }
}
