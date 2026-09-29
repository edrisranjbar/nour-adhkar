<?php

namespace App\Services;

use App\Mail\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * One-time 5-digit email codes, delivered with the configured mailer (Resend).
 * Codes are stored hashed, expire after EXPIRES_MINUTES, and allow MAX_ATTEMPTS guesses.
 * This class handles email verification; PasswordResetCodeService reuses it for resets.
 */
class EmailVerificationService
{
    public const EXPIRES_MINUTES = 10;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    /** Table holding the hashed codes for this purpose. */
    protected function table(): string
    {
        return 'email_verification_codes';
    }

    /** 'verify' or 'reset'; selects the email wording. */
    protected function purpose(): string
    {
        return 'verify';
    }

    /** Runs after a correct code, before the code is consumed. */
    protected function onVerified(User $user): void
    {
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    /** Seconds until another code may be sent for this email (0 when allowed now). */
    public function cooldownRemaining(string $email): int
    {
        $row = DB::table($this->table())->where('email', $email)->first();
        if (!$row) {
            return 0;
        }
        $elapsed = now()->getTimestamp() - \Illuminate\Support\Carbon::parse($row->last_sent_at)->getTimestamp();
        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    /** Creates a fresh code and emails it. Returns false (sending nothing) while in cooldown. */
    public function send(User $user): bool
    {
        if ($this->cooldownRemaining($user->email) > 0) {
            return false;
        }

        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        DB::table($this->table())->updateOrInsert(
            ['email' => $user->email],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
                'last_sent_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new EmailVerificationCode($code, $user->name, self::EXPIRES_MINUTES, $this->purpose()));
        return true;
    }

    /**
     * Checks a code. Returns one of: 'ok', 'invalid', 'expired', 'too_many'.
     * A successful check runs onVerified() and consumes the code.
     */
    public function verify(User $user, string $code): string
    {
        $row = DB::table($this->table())->where('email', $user->email)->first();
        if (!$row) {
            return 'expired';
        }
        if (now()->greaterThan($row->expires_at)) {
            return 'expired';
        }
        if ($row->attempts >= self::MAX_ATTEMPTS) {
            return 'too_many';
        }
        if (!Hash::check($code, $row->code_hash)) {
            DB::table($this->table())->where('email', $user->email)->increment('attempts');
            return $row->attempts + 1 >= self::MAX_ATTEMPTS ? 'too_many' : 'invalid';
        }

        $this->onVerified($user);
        DB::table($this->table())->where('email', $user->email)->delete();
        return 'ok';
    }
}
