<?php

use App\Mail\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function appHeaders(): array
{
    return ['X-Nour-Client' => 'android'];
}

function sentCode(): string
{
    $code = null;
    Mail::assertSent(EmailVerificationCode::class, function ($mail) use (&$code) {
        $code = $mail->code;
        return true;
    });
    return $code;
}

beforeEach(fn () => Mail::fake());

it('asks app registrations to verify a 5-digit code before issuing a token', function () {
    $this->withHeaders(appHeaders())->postJson('/api/auth/register', [
        'name' => 'Reader', 'email' => 'reader@example.com', 'password' => 'secret12',
    ])->assertCreated()->assertJsonPath('verification_required', true)->assertJsonMissingPath('token');

    $code = sentCode();
    expect($code)->toMatch('/^\d{5}$/');

    $this->postJson('/api/auth/verify-email', ['email' => 'reader@example.com', 'password' => 'secret12', 'code' => $code])
        ->assertOk()->assertJsonStructure(['token', 'user']);
    expect(User::first()->email_verified_at)->not->toBeNull();
});

it('keeps website registration and login unchanged', function () {
    $this->postJson('/api/auth/register', [
        'name' => 'Web', 'email' => 'web@example.com', 'password' => 'secret12',
    ])->assertCreated()->assertJsonStructure(['token']);
    $this->postJson('/api/auth/login', ['email' => 'web@example.com', 'password' => 'secret12'])
        ->assertOk()->assertJsonStructure(['token']);
    Mail::assertNotSent(EmailVerificationCode::class);
});

it('requires unverified users to verify on first app login', function () {
    User::create(['name' => 'Old', 'email' => 'old@example.com', 'password' => Hash::make('secret12')]);

    $this->withHeaders(appHeaders())->postJson('/api/auth/login', ['email' => 'old@example.com', 'password' => 'secret12'])
        ->assertForbidden()->assertJsonPath('verification_required', true);
    Mail::assertSent(EmailVerificationCode::class);
});

it('rejects a wrong password, wrong codes, and locks after too many attempts', function () {
    $this->withHeaders(appHeaders())->postJson('/api/auth/register', [
        'name' => 'Reader', 'email' => 'reader@example.com', 'password' => 'secret12',
    ]);
    $code = sentCode();
    $wrong = $code === '00000' ? '11111' : '00000';

    $this->postJson('/api/auth/verify-email', ['email' => 'reader@example.com', 'password' => 'nope', 'code' => $code])
        ->assertUnauthorized();
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/auth/verify-email', ['email' => 'reader@example.com', 'password' => 'secret12', 'code' => $wrong])
            ->assertUnprocessable();
    }
    // Even the right code is refused once the attempt limit is reached.
    $this->postJson('/api/auth/verify-email', ['email' => 'reader@example.com', 'password' => 'secret12', 'code' => $code])
        ->assertUnprocessable();
});

it('never issues a token through verify for an already verified account', function () {
    $user = User::create(['name' => 'V', 'email' => 'v@example.com', 'password' => Hash::make('secret12')]);
    $user->forceFill(['email_verified_at' => now()])->save();

    $this->postJson('/api/auth/verify-email', ['email' => 'v@example.com', 'password' => 'secret12', 'code' => '12345'])
        ->assertStatus(409)->assertJsonMissingPath('token');
});

it('enforces a resend cooldown', function () {
    $this->withHeaders(appHeaders())->postJson('/api/auth/register', [
        'name' => 'Reader', 'email' => 'reader@example.com', 'password' => 'secret12',
    ]);
    $this->postJson('/api/auth/resend-code', ['email' => 'reader@example.com'])->assertStatus(429);

    $this->travel(61)->seconds();
    $this->postJson('/api/auth/resend-code', ['email' => 'reader@example.com'])->assertOk();
    Mail::assertSent(EmailVerificationCode::class, 2);
});

it('answers in Persian instead of a raw Server Error when the codes table is missing', function () {
    User::create(['name' => 'Old', 'email' => 'old@example.com', 'password' => Hash::make('secret12')]);
    Illuminate\Support\Facades\Schema::drop('email_verification_codes');

    foreach ([
        ['/api/auth/login', ['email' => 'old@example.com', 'password' => 'secret12']],
        ['/api/auth/resend-code', ['email' => 'old@example.com']],
        ['/api/auth/verify-email', ['email' => 'old@example.com', 'password' => 'secret12', 'code' => '12345']],
    ] as [$url, $body]) {
        $response = $this->withHeaders(appHeaders())->postJson($url, $body)->assertStatus(500);
        expect($response->json('message'))->toMatch('/\p{Arabic}/u')->not->toBe('Server Error');
    }
});
