<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, TenantContext $tenantContext, TenantSecurityService $securityService): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $tenant = $tenantContext->tenant();
        if ($tenant) {
            $securityService->bindAuthenticatedUser($request, $tenant, Auth::guard('web')->user());
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $tenantSlug = $request->session()->get('current_tenant_slug');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $tenantSlug
            ? redirect()->route('login', ['tenant' => $tenantSlug])
            : redirect('/');
    }
}
