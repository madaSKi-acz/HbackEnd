<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\WebAuthnController;

Route::post('/login', [AuthTokenController::class, 'login']);
Route::post('/logout', [AuthTokenController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/webauthn/register/options', [WebAuthnController::class, 'registerOptions']);
    Route::post('/webauthn/register/verify', [WebAuthnController::class, 'registerVerify']);
});

Route::middleware(['auth:sanctum', 'webauthn.bound'])->group(function () {
    Route::apiResource('users', UsersController::class);
});