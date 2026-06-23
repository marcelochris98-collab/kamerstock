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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'checkauth'  => \App\Http\Middleware\CheckAuth::class,
            'audit'      => \App\Http\Middleware\AuditRequests::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('landlord/*') || $request->is('landlord')) {
                return route('landlord.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
