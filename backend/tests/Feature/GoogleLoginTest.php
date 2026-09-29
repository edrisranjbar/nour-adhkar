<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const TEST_CLIENT_ID = 'android-web-client.apps.googleusercontent.com';
const GOOGLE_CERTS = 'https://www.googleapis.com/oauth2/v3/certs';
const MIRROR_CERTS = 'https://mirror.test/google-jwks.json';

function b64url(string $v): string
{
    return rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
}

/** RSA key pair plus its JWKS entry, like Google publishes. */
function testKey(string $kid = 'kid-1'): array
{
    $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
    // Windows PHP builds need an explicit openssl.cnf to generate keys.
    $cnf = getenv('OPENSSL_CONF') ?: dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
    if (is_file($cnf)) {
        $options['config'] = $cnf;
    }
    $key = openssl_pkey_new($options);
    $rsa = openssl_pkey_get_details($key)['rsa'];
    return [$key, ['kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => $kid, 'n' => b64url($rsa['n']), 'e' => b64url($rsa['e'])]];
}

function idToken($privateKey, string $kid, array $overrides = []): string
{
    $claims = array_merge([
        'iss' => 'https://accounts.google.com',
        'aud' => TEST_CLIENT_ID,
        'email' => 'reader@example.com',
        'email_verified' => true,
        'name' => 'Reader',
        'iat' => time(),
        'exp' => time() + 3600,
    ], $overrides);
    $head = b64url(json_encode(['alg' => 'RS256', 'kid' => $kid, 'typ' => 'JWT']));
    $body = b64url(json_encode($claims));
    openssl_sign("$head.$body", $signature, $privateKey, OPENSSL_ALGO_SHA256);
    return "$head.$body." . b64url($signature);
}

beforeEach(function () {
    Cache::flush();
    config([
        'services.google.client_ids' => TEST_CLIENT_ID,
        'services.google.jwks_urls' => GOOGLE_CERTS . ',' . MIRROR_CERTS,
    ]);
});

it('verifies the token locally using the mirror when Google is unreachable, and reuses the user', function () {
    [$key, $jwk] = testKey();
    Http::fake([
        GOOGLE_CERTS => fn () => throw new Illuminate\Http\Client\ConnectionException('blocked'),
        MIRROR_CERTS => Http::response(['keys' => [$jwk]]),
    ]);

    $this->postJson('/api/auth/google', ['id_token' => idToken($key, 'kid-1')])
        ->assertCreated()->assertJsonPath('user.email', 'reader@example.com')->assertJsonStructure(['token']);
    $this->postJson('/api/auth/google', ['id_token' => idToken($key, 'kid-1')])->assertOk();
    expect(User::count())->toBe(1)->and(User::first()->email_verified_at)->not->toBeNull();
});

it('rejects wrong audience, expired, unverified-email and tampered tokens', function () {
    [$key, $jwk] = testKey();
    [$otherKey] = testKey();
    Http::fake([MIRROR_CERTS => Http::response(['keys' => [$jwk]]), GOOGLE_CERTS => Http::response([], 500)]);

    foreach ([
        idToken($key, 'kid-1', ['aud' => 'someone-else']),
        idToken($key, 'kid-1', ['exp' => time() - 3600]),
        idToken($key, 'kid-1', ['email_verified' => false]),
        idToken($otherKey, 'kid-1'),              // signed with a different key
        idToken($key, 'unknown-kid'),
        'not-a-jwt',
    ] as $token) {
        $this->postJson('/api/auth/google', ['id_token' => $token])->assertUnauthorized();
    }
    expect(User::count())->toBe(0);
});

it('refetches keys for an unknown key id at most once per five minutes', function () {
    [$key, $jwk] = testKey();
    Http::fake([MIRROR_CERTS => Http::response(['keys' => [$jwk]]), GOOGLE_CERTS => Http::response([], 500)]);

    $this->postJson('/api/auth/google', ['id_token' => idToken($key, 'kid-1')])->assertCreated();
    $before = count(Http::recorded());
    foreach (range(1, 3) as $_) {
        $this->postJson('/api/auth/google', ['id_token' => idToken($key, 'made-up')])->assertUnauthorized();
    }
    // One refetch (Google + mirror) for the first unknown id, none for the rest.
    expect(count(Http::recorded()) - $before)->toBe(2);
});

it('answers with a friendly Persian 503 when no key source is reachable', function () {
    [$key] = testKey();
    Http::fake(['*' => Http::response([], 500)]);

    $response = $this->postJson('/api/auth/google', ['id_token' => idToken($key, 'kid-1')])->assertStatus(503);
    expect($response->json('message'))->toMatch('/\p{Arabic}/u');
});
