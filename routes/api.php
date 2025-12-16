<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

Route::apiResource('users', UsersController::class);
