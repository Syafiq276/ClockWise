<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers for all web requests
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);
        
        // Network context detection (office/remote)
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckNetworkContext::class);

        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // Rate limiting for authentication routes
        $middleware->throttleApi('60,1'); // 60 requests per minute for API
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
