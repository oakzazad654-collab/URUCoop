<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class CwieWorkflowMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        return $this->subject($this->payload['title'].' · URU Coop-IS')
            ->view('emails.cwie-workflow', ['payload' => $this->payload]);
    }
}