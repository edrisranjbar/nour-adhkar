<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The API sits behind the ArvanCloud CDN: trust its forwarded headers so request()->ip()
        // is the visitor's address (analytics, rate limits) rather than a CDN edge server.
        // Narrow TRUSTED_PROXIES to the CDN's ranges (comma-separated) if the origin is reachable directly.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
