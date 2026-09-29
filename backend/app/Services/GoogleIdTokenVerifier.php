<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Verifies Google ID tokens locally (RS256 signature + claims) instead of calling Google's
 * tokeninfo endpoint per login. Google's APIs are not reachable from servers in Iran, so the
 * public signing keys (JWKS) are loaded from a list of URLs: Google first, then a mirror kept
 * up to date by a scheduled GitHub Action. The last good key set is cached for a week so short
 * outages of every source do not break sign-in.
 */
class GoogleIdTokenVerifier
{
    private const CACHE_KEY = 'google_jwks';
    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];
    private const LEEWAY_SECONDS = 120;

    /** Thrown when no key source can be reached (not the user's fault). */
    public const KEYS_UNAVAILABLE = 'keys_unavailable';

    /**
     * Returns the verified claims, or throws RuntimeException. The exception message is
     * KEYS_UNAVAILABLE when the keys cannot be loaded, otherwise the token itself is invalid.
     */
    public function verify(string $idToken, array $clientIds): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new RuntimeException('malformed token');
        }
        [$headerB64, $payloadB64, $signatureB64] = $parts;
        $header = json_decode($this->b64url($headerB64), true);
        $claims = json_decode($this->b64url($payloadB64), true);
        $signature = $this->b64url($signatureB64);
        if (!is_array($header) || !is_array($claims) || ($header['alg'] ?? null) !== 'RS256' || empty($header['kid'])) {
            throw new RuntimeException('unsupported token header');
        }

        $key = $this->publicKey($header['kid']);
        if (openssl_verify("$headerB64.$payloadB64", $signature, $key, OPENSSL_ALGO_SHA256) !== 1) {
            throw new RuntimeException('bad signature');
        }

        $now = time();
        $emailVerified = in_array($claims['email_verified'] ?? null, [true, 'true'], true);
        if (
            !in_array($claims['aud'] ?? null, $clientIds, true)
            || !in_array($claims['iss'] ?? null, self::ISSUERS, true)
            || (int) ($claims['exp'] ?? 0) < $now - self::LEEWAY_SECONDS
            || (int) ($claims['iat'] ?? PHP_INT_MAX) > $now + self::LEEWAY_SECONDS
            || empty($claims['email'])
            || !$emailVerified
        ) {
            throw new RuntimeException('claims rejected');
        }

        return $claims;
    }

    /** PEM public key for a key id, refreshing the key set once if the id is unknown. */
    private function publicKey(string $kid): string
    {
        $keys = Cache::get(self::CACHE_KEY);
        // Refetch when there are no keys, or for an unknown key id at most once per 5 minutes, so
        // tokens with made-up key ids cannot trigger an outbound request on every call.
        if (!is_array($keys) || (!isset($keys[$kid]) && Cache::add(self::CACHE_KEY . '_refetch', 1, 300))) {
            $keys = $this->fetchKeys() ?? $keys;
        }
        if (!is_array($keys)) {
            throw new RuntimeException(self::KEYS_UNAVAILABLE);
        }
        if (!isset($keys[$kid])) {
            throw new RuntimeException('unknown key id');
        }
        return $keys[$kid];
    }

    /** kid => PEM from the first source that answers with a valid JWKS; null if none does. */
    private function fetchKeys(): ?array
    {
        $sources = array_filter(array_map('trim', explode(',', (string) config('services.google.jwks_urls'))));
        foreach ($sources as $url) {
            try {
                $response = Http::timeout(5)->connectTimeout(3)->get($url);
                $jwks = $response->successful() ? $response->json('keys') : null;
                $pems = [];
                foreach (is_array($jwks) ? $jwks : [] as $jwk) {
                    if (($jwk['kty'] ?? null) === 'RSA' && !empty($jwk['kid']) && !empty($jwk['n']) && !empty($jwk['e'])) {
                        $pems[$jwk['kid']] = $this->rsaPem($jwk['n'], $jwk['e']);
                    }
                }
                if ($pems) {
                    Cache::put(self::CACHE_KEY, $pems, now()->addDays(7));
                    return $pems;
                }
            } catch (\Throwable $e) {
                Log::warning("Google JWKS source failed ($url): " . $e->getMessage());
            }
        }
        return null;
    }

    /** Builds a PEM SubjectPublicKeyInfo for an RSA key from its base64url modulus and exponent. */
    private function rsaPem(string $n, string $e): string
    {
        $modulus = $this->b64url($n);
        $exponent = $this->b64url($e);
        if (ord($modulus[0]) > 0x7f) {
            $modulus = "\x00" . $modulus;
        }
        if (ord($exponent[0]) > 0x7f) {
            $exponent = "\x00" . $exponent;
        }
        $rsaKey = $this->der(0x30, $this->der(0x02, $modulus) . $this->der(0x02, $exponent));
        $algorithm = $this->der(0x30, "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00"); // rsaEncryption, NULL
        $spki = $this->der(0x30, $algorithm . $this->der(0x03, "\x00" . $rsaKey));
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    private function der(int $tag, string $value): string
    {
        $length = strlen($value);
        if ($length < 0x80) {
            return chr($tag) . chr($length) . $value;
        }
        $bytes = ltrim(pack('N', $length), "\x00");
        return chr($tag) . chr(0x80 | strlen($bytes)) . $bytes . $value;
    }

    private function b64url(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4));
    }
}
