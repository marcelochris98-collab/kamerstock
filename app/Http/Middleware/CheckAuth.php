<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class CheckAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(\App\Services\Platform\SupportContext::class)->isSupportMode()) {
            return $next($request);
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user && !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        // Mettre à jour last_login
        if (!$user->last_login || $user->last_login->diffInMinutes(now()) > 30) {
            $user->updateQuietly(['last_login' => now()]);
        }

        return $next($request);
    }
}