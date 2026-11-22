<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    public function __construct(private TenantSecurityService $securityService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (! $this->securityService->validateAuthenticatedTenantSession($request, $user)) {
            auth()->logout();
            $request->session()->invalidate();

            return redirect()->route('login')
                ->withErrors(['tenant' => 'Votre session ne correspond pas à cet espace entreprise.']);
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        // Mettre à jour last_login
        if (! $user->last_login || $user->last_login->diffInMinutes(now()) > 30) {
            $user->updateQuietly(['last_login' => now()]);
        }

        return $next($request);
    }
}
