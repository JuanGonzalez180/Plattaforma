<?php

namespace App\Mail\QueryWall;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendQuestionQuoteMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "La compañia ";

    protected $companyName;
    protected $quoteName;
    protected $quoteId;
    protected $slugCompany;
    protected $question;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $companyName, string $quoteName, string $quoteId, string $slugCompany, string $question)
    {
        $this->companyName  = $companyName;
        $this->quoteName    = $quoteName;
        $this->quoteId      = $quoteId;
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
        return $this->view('emails.queryWall.send-question-quote-user')
            ->subject($this->subject . $this->companyName . ' ha realizado una pregunta en una cotización.')
            ->with([
                'companyName'       => $this->companyName,
                'quoteName'         => $this->quoteName,
                'quoteId'           => $this->quoteId,
                'slugCompany'       => $this->slugCompany,
                'question'          => $this->question,
            ]);
    }
}
