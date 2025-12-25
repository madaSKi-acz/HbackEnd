<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebAuthnController extends Controller
{
    public function registerOptions(Request $request)
    {
        Log::info("register option");
        $user = $request->user();

        if (!$user) {
            Log::warning('WebAuthn options: No user');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $challenge = random_bytes(32);

        // Store challenge in SESSION (secure, not DB)
        $request->session()->put('webauthn_register_challenge', $challenge);

        Log::info('WebAuthn options', ['user_id' => $user->id]);

        return response()->json([
            'challenge' => base64_encode($challenge),  // base64 for JS
            'rp' => [
                'name' => config('app.name'),
                'id' => parse_url(config('app.url'), PHP_URL_HOST),
            ],
            'user' => [
                'id' => base64_encode($user->id),  // base64 Uint8Array in JS
                'name' => $user->email,
                'displayName' => $user->name ?? $user->email,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],  // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ]);
    }

    public function registerVerify(Request $request)
    {
        Log::info("register verify");
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $storedChallenge = $request->session()->get('webauthn_register_challenge');
        if (!$storedChallenge) {
            return response()->json(['error' => 'Invalid challenge'], 400);
        }

        // Decode base64 from JS
        $credentialId = $request->input('id');
        $attestationObject = base64_decode($request->input('response.attestationObject'));
        $clientDataJSON = base64_decode($request->input('response.clientDataJSON'));

        // TODO: Full verify (use webauthn lib like asbiin/laravel-webauthn for prod)
        // Simple insert for lab (no sig/crypto verify – add later)
        DB::table('webauthn_credentials')->insert([
            'user_id' => $user->id,
            'credential_id' => $credentialId,
            'public_key' => base64_encode($attestationObject),  // Store full for verify later
            'sign_count' => 0,
            'transports' => json_encode(['usb', 'internal']),
        ]);

        $request->session()->forget('webauthn_register_challenge');

        Log::info('WebAuthn registered', ['user_id' => $user->id, 'credential_id' => $credentialId]);

        return response()->json(['message' => 'WebAuthn device bound successfully!']);
    }
}