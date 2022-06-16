<?php

namespace App\Mail\QueryWall;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendQuestionMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La compañia ";

    protected $companyName;
    protected $tenderName;
    protected $tenderId;
    protected $slugCompany;
    protected $question;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $tenderName, string $tenderId, string $slugCompany, string $question)
    {
        $this->companyName  = $companyName;
        $this->tenderName   = $tenderName;
        $this->tenderId     = $tenderId;
        $this->slugCompany  = $slugCompany;
        $this->question     = $question;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.queryWall.send-question-user')
            ->subject($this->subject . $this->companyName.' ha realizado una pregunta.')
            ->with([
                'companyName'       => $this->companyName,
                'tenderName'        => $this->tenderName,
                'tenderId'          => $this->tenderId,
                'slugCompany'       => $this->slugCompany,
                'question'          => $this->question,
            ]);
    }
}
