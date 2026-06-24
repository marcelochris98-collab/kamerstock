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
            'identify.tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'identify.support' => \App\Http\Middleware\IdentifySupportAccess::class,
            'tenant.subscription' => \App\Http\Middleware\EnsureTenantSubscriptionIsValid::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\IdentifySupportAccess::class,
            \App\Http\Middleware\EnsureTenantSubscriptionIsValid::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('landlord/*') || $request->is('landlord')) {
                return route('landlord.login');
            }
            return route('login');
        });

        $middleware->redirectUsersTo(function ($request) {
            if (auth()->guard('landlord')->check()) {
                return route('landlord.dashboard');
            }
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
