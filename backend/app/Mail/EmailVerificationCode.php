<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Branded Adhkar Nour email carrying a one-time 5-digit code (sent through Resend).
 * $purpose is 'verify' (confirm email) or 'reset' (password reset); it changes the wording.
 */
class EmailVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public ?string $name,
        public int $expiresInMinutes,
        public string $purpose = 'verify',
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'reset' ? 'کد بازیابی رمز عبور اذکار نور: ' : 'کد تأیید ایمیل اذکار نور: ';
        return new Envelope(subject: $subject . $this->code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification_code_fa',
            text: 'emails.verification_code_fa_text',
        );
    }
}
