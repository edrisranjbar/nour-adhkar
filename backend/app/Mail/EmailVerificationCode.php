<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Branded Adhkar Nour email carrying a one-time 5-digit verification code (sent through Resend). */
class EmailVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public ?string $name,
        public int $expiresInMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'کد تأیید ایمیل اذکار نور: ' . $this->code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification_code_fa',
            text: 'emails.verification_code_fa_text',
        );
    }
}
