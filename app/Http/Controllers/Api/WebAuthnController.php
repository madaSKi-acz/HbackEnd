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
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $challenge = random_bytes(32);
        $request->session()->put('webauthn_register_challenge', $challenge);

        return response()->json([
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => config('app.name'),
                'id' => parse_url(config('app.url'), PHP_URL_HOST),
            ],
            'user' => [
                'id' => base64_encode($user->id),
                'name' => $user->email,
                'displayName' => $user->name ?? $user->email,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ]);
    }

    public function registerVerify(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $storedChallenge = $request->session()->get('webauthn_register_challenge');
        if (!$storedChallenge) {
            return response()->json(['error' => 'Invalid challenge'], 400);
        }

        $credentialId = $request->input('id');
        $attestationObject = base64_decode($request->input('response.attestationObject'));
        // $clientDataJSON = base64_decode($request->input('response.clientDataJSON'));

        DB::table('webauthn_credentials')->insert([
            'user_id' => $user->id,
            'credential_id' => $credentialId,
            'public_key' => base64_encode($attestationObject),
            'sign_count' => 0,
            'transports' => json_encode(['usb', 'internal']),
        ]);

        $request->session()->forget('webauthn_register_challenge');
        return response()->json(['message' => 'WebAuthn device bound successfully!']);
    }
}