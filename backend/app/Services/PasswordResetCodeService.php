<?php

namespace App\Services;

use App\Models\User;

/**
 * 5-digit password reset codes for the Android app. Same rules as email verification
 * (hashed, 10 min expiry, 5 attempts, 60 s resend cooldown) in a separate table, so a
 * pending verification code and a reset code never overwrite each other.
 */
class PasswordResetCodeService extends EmailVerificationService
{
    protected function table(): string
    {
        return 'password_reset_codes';
    }

    protected function purpose(): string
    {
        return 'reset';
    }

    /** A correct reset code also proves ownership of the email. */
    protected function onVerified(User $user): void
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
