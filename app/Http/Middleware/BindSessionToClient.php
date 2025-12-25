<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BindSessionToClient
{
    public function handle(Request $request, Closure $next)
    {
        // ll
        Log::info("bind session functions");
        // Log::info($request);
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user has ANY WebAuthn credential
        $hasCredential = DB::table('webauthn_credentials')
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasCredential) {
            Log::info('WebAuthn Middleware BLOCK: User ID ' . $user->id . ' has no credential yet (register first)');
            return response()->json(
                ['message' => 'WebAuthn verification required'],
                403
            );
        }

        Log::debug('WebAuthn Middleware PASS: User ID ' . $user->id);

        // Optional: Bind client_id from session to credential (anti-hijack)
        $sessionClientId = $request->session()->get('client_id');
        if ($sessionClientId) {
            // Update credential with client_id if needed (extend table if want per-device)
        }

        return $next($request);
    }
}