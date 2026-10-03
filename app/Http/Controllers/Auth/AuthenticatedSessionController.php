<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Platform\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Passe le slug tenant à la vue pour l'injecter dans le formulaire
        $tenantSlug = request()->query('tenant');
        return view('auth.login', compact('tenantSlug'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $tenantSlug = $request->input('tenant');

        if ($tenantSlug) {
            $tenant = Tenant::on('landlord')
                ->where('slug', $tenantSlug)
                ->where('provisioning_status', 'migrated')
                ->first();

            if ($tenant) {
                app(\App\Services\Tenancy\TenantDatabaseManager::class)->switchToTenant($tenant);
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        // Récupérer le slug résolu pendant l'authentification s'il n'était pas fourni au départ
        $tenantSlug = $request->input('tenant') ?? session('current_tenant_slug');

        if ($tenantSlug) {
            $request->session()->put('current_tenant_slug', $tenantSlug);
            $request->session()->forget('url.intended');
            return redirect(route('dashboard', ['tenant' => $tenantSlug], false));
        } else {
            $request->session()->forget('current_tenant_slug');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->forget('current_tenant_slug');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}