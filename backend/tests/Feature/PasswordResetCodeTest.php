<?php

use App\Mail\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => Mail::fake());

function resetCodeSent(): string
{
    $code = null;
    Mail::assertSent(EmailVerificationCode::class, function ($mail) use (&$code) {
        if ($mail->purpose === 'reset') {
            $code = $mail->code;
        }
        return $mail->purpose === 'reset';
    });
    return $code;
}

it('resets the password with an emailed 5-digit code and signs in', function () {
    User::create(['name' => 'Reader', 'email' => 'reader@example.com', 'password' => Hash::make('old-pass1')]);

    $this->postJson('/api/auth/password-reset/request', ['email' => 'reader@example.com'])->assertOk();
    $code = resetCodeSent();
    expect($code)->toMatch('/^\d{5}$/');

    $this->postJson('/api/auth/password-reset/confirm', ['email' => 'reader@example.com', 'code' => $code, 'password' => 'new-pass1'])
        ->assertOk()->assertJsonStructure(['token', 'user']);

    $user = User::first();
    expect(Hash::check('new-pass1', $user->password))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
    // The code is single-use.
    $this->postJson('/api/auth/password-reset/confirm', ['email' => 'reader@example.com', 'code' => $code, 'password' => 'other-pass'])
        ->assertUnprocessable();
});

it('does not reveal whether an email is registered', function () {
    $this->postJson('/api/auth/password-reset/request', ['email' => 'nobody@example.com'])->assertOk();
    Mail::assertNothingSent();
    $this->postJson('/api/auth/password-reset/confirm', ['email' => 'nobody@example.com', 'code' => '12345', 'password' => 'new-pass1'])
        ->assertUnprocessable()->assertJsonPath('message', 'کد بازیابی نادرست است.');
});

it('rejects wrong codes, locks after five attempts, and keeps the old password', function () {
    User::create(['name' => 'Reader', 'email' => 'reader@example.com', 'password' => Hash::make('old-pass1')]);
    $this->postJson('/api/auth/password-reset/request', ['email' => 'reader@example.com']);
    $code = resetCodeSent();
    $wrong = $code === '00000' ? '11111' : '00000';

    foreach (range(1, 5) as $_) {
        $this->postJson('/api/auth/password-reset/confirm', ['email' => 'reader@example.com', 'code' => $wrong, 'password' => 'new-pass1'])
            ->assertUnprocessable();
    }
    $this->postJson('/api/auth/password-reset/confirm', ['email' => 'reader@example.com', 'code' => $code, 'password' => 'new-pass1'])
        ->assertUnprocessable();
    expect(Hash::check('old-pass1', User::first()->password))->toBeTrue();
});

it('validates the new password and enforces a resend cooldown', function () {
    User::create(['name' => 'Reader', 'email' => 'reader@example.com', 'password' => Hash::make('old-pass1')]);
    $this->postJson('/api/auth/password-reset/request', ['email' => 'reader@example.com'])->assertOk();
    $this->postJson('/api/auth/password-reset/request', ['email' => 'reader@example.com'])->assertStatus(429);

    $this->postJson('/api/auth/password-reset/confirm', ['email' => 'reader@example.com', 'code' => resetCodeSent(), 'password' => '123'])
        ->assertUnprocessable()->assertJsonPath('message', 'رمز عبور تازه باید حداقل ۶ کاراکتر باشد.');
});

it('keeps verification and reset codes separate', function () {
    $this->withHeaders(['X-Nour-Client' => 'android'])->postJson('/api/auth/register', [
        'name' => 'Reader', 'email' => 'reader@example.com', 'password' => 'secret12',
    ])->assertCreated();
    $this->postJson('/api/auth/password-reset/request', ['email' => 'reader@example.com'])->assertOk();

    Mail::assertSent(EmailVerificationCode::class, fn ($m) => $m->purpose === 'verify');
    Mail::assertSent(EmailVerificationCode::class, fn ($m) => $m->purpose === 'reset');
    expect(DB::table('email_verification_codes')->count())->toBe(1)
        ->and(DB::table('password_reset_codes')->count())->toBe(1);
});
