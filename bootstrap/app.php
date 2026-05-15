<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            '2fa' => \App\Http\Middleware\Require2FA::class,
        ]);
        

        $middleware->validateCsrfTokens(except: [
            'login',
            'forgot-password',
            'reset-password',
            'register',
            'posts',
            'files/convert',
        ]);

        // ✅ FIX: Daftarkan Middleware Keamanan HTTP Headers secara global
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    
