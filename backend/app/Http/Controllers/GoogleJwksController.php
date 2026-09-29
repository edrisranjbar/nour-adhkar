<?php

namespace App\Http\Controllers;

use App\Services\GoogleIdTokenVerifier;
use Illuminate\Http\Request;

/**
 * Receives Google's public sign-in keys from the Nour-Adhkar-App GitHub workflow, because this
 * server cannot reach Google or GitHub's raw hosts. Protected by GOOGLE_JWKS_PUSH_TOKEN.
 */
class GoogleJwksController extends Controller
{
    public function store(Request $request, GoogleIdTokenVerifier $verifier)
    {
        $expected = (string) config('services.google.jwks_push_token');
        $given = (string) $request->bearerToken();
        if ($expected === '' || !hash_equals($expected, $given)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $count = $verifier->storeKeys($request->input('keys'));
        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'No valid RSA keys'], 422);
        }

        return response()->json(['success' => true, 'stored' => $count]);
    }
}
