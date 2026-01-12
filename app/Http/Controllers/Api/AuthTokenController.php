<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class AuthTokenController extends Controller
{
    public function login(Request $request)
    {
        Log::info("login function");
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Use WEB guard for SPA session auth (Sanctum expects this)
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // Generate/store client_id (random UUID for binding)
        $clientId = (string) Str::uuid();
        $request->session()->put('client_id', $clientId);

        Log::info('Login success', ['user_id' => $user->id, 'client_id' => $clientId]);

        return response()->json(['message' => 'Logged in successfully']);
    }

    public function logout(Request $request)
    {
        Log::info('Logout', ['user_id' => Auth::id()]);

        // 1. Logout the user from the web guard
        Auth::guard('web')->logout();

        // 2. Invalidate the session
        $request->session()->invalidate();

        // 3. Regenerate CSRF token
        $request->session()->regenerateToken();

        // 4. Explicitly forget sensitive cookies
        $cookieName = config('session.cookie'); // usually "laravel_session"
        Cookie::queue(Cookie::forget($cookieName));
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        // 5. (Optional) Revoke WebAuthn credentials if you want logout to remove them
        if ($user = Auth::user()) {
            $user->webauthnCredentials()->delete();
            Log::info('WebAuthn credentials revoked', ['user_id' => $user->id]);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
