<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => config(['services.google.client_ids' => 'android-web-client.apps.googleusercontent.com']));

it('creates a user from a verified google id token and reuses it later', function () {
    Http::fake(['oauth2.googleapis.com/*' => Http::response([
        'aud' => 'android-web-client.apps.googleusercontent.com',
        'iss' => 'https://accounts.google.com',
        'email' => 'reader@example.com',
        'email_verified' => 'true',
        'name' => 'Reader',
    ])]);

    $this->postJson('/api/auth/google', ['id_token' => 'token'])->assertCreated()->assertJsonPath('user.email', 'reader@example.com')->assertJsonStructure(['token']);
    $this->postJson('/api/auth/google', ['id_token' => 'token'])->assertOk();
    expect(User::count())->toBe(1);
});

it('rejects tokens for another audience', function () {
    Http::fake(['oauth2.googleapis.com/*' => Http::response([
        'aud' => 'someone-else', 'iss' => 'accounts.google.com', 'email' => 'x@example.com', 'email_verified' => true,
    ])]);

    $this->postJson('/api/auth/google', ['id_token' => 'token'])->assertUnauthorized();
    expect(User::count())->toBe(0);
});
