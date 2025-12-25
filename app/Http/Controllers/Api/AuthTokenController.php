<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        Auth::guard('web')->login($user);  // TRUE = remember me
        $request->session()->regenerate();

        // Generate/store client_id (random UUID for binding)
        $clientId = (string) Str::uuid();
        $request->session()->put('client_id', $clientId);

        Log::info('Login success', ['user_id' => $user->id, 'client_id' => $clientId]);

        return response()->json(['message' => 'Logged in successfully']);
    }

    /**
     * Logout (revoke session)
     */
    public function logout(Request $request)
    {
        Log::info('Logout', ['user_id' => Auth::id()]);

        Auth::guard('web')->logout();  // Clears the session

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}