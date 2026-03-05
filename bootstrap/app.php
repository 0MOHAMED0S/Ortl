<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);

$middleware->validateCsrfTokens(except: [
        'api/student/paytabs/*',
        'api/student/paytabs/response',
        'api/student/paytabs/callback',

        'api/gifts/payment/callback', // مسار الـ Webhook (إن كان يطبق عليه الـ CSRF)
            'gifts/payment/response',     // 🚨 أهم مسار لأنه في web.php وسيستقبل POST من المتصفح
            'gifts/payment/*',
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
