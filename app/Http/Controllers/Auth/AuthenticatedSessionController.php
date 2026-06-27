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

        // ✅ CORRECTION PRINCIPALE :
        // Si un slug tenant est présent, on switche la connexion DB vers la DB
        // de ce tenant AVANT que LoginRequest::authenticate() soit appelé.
        // Sans ça, Laravel cherche l'utilisateur dans la DB principale (kamerstock)
        // au lieu de la DB tenant (ex: kamerstock_boutique_test) → "identifiants incorrects".
        if ($tenantSlug) {
            $tenant = Tenant::on('landlord')
                ->where('slug', $tenantSlug)
                ->where('provisioning_status', 'migrated')
                ->first();

            if ($tenant) {
                // Reconfigurer la connexion tenant dynamiquement
                config(['database.connections.tenant.database' => $tenant->database_name]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::reconnect('tenant');
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        // ✅ CORRECTION : On force la redirection vers dashboard avec ?tenant=slug.
        // redirect()->intended() est ignoré ici car il utiliserait l'URL sauvegardée
        // en session (sans le paramètre tenant) et ferait perdre le contexte tenant.
        if ($tenantSlug) {
            $request->session()->put('current_tenant_slug', $tenantSlug);
            // Vider l'URL "intended" pour éviter qu'elle soit utilisée sans le tenant
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