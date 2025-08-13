<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordFa extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendBase = config('app.frontend_url', config('app.url'));
        $email = $notifiable->getEmailForPasswordReset();
        $resetUrl = rtrim($frontendBase, '/') . '/reset-password?token=' . urlencode($this->token) . '&email=' . urlencode($email);

        return (new MailMessage)
            ->subject('بازیابی رمز عبور')
            ->view('emails.reset_password_fa', [
                'resetUrl' => $resetUrl,
                'user' => $notifiable,
            ]);
    }
}


