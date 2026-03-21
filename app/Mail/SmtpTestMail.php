<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $appName) {}

    public function build(): self
    {
        return $this->subject($this->appName.' SMTP Test Email')
            ->text('emails.smtp-test');
    }

    public function appName(): string
    {
        return $this->appName;
    }
}
