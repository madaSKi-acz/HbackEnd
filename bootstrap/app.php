<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // clients verification.
        // $middleware->api(append: [
        //     \App\Http\Middleware\BindSessionToClient::class,
        // ]);
        $middleware->alias([
            'webauthn.bound' => \App\Http\Middleware\BindSessionToClient::class,
            // Add others if needed
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
