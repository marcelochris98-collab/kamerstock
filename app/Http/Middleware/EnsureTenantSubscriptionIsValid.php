<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Tenancy\TenantContext;
use App\Models\Platform\Tenant;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsValid
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ignorer les routes landlord
        if ($request->is('landlord/*') || $request->is('landlord') || $request->routeIs('landlord.*')) {
            return $next($request);
        }

        // 2. Ignorer les routes techniques
        $ignoredRoutes = [
            'tenant.pending',
            'tenant.suspended',
            'tenant.billing',
            'tenant.read_only',
            'logout',
            'assets',
            'build',
            'storage'
        ];

        foreach ($ignoredRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        if ($request->is('assets/*') || $request->is('build/*') || $request->is('storage/*')) {
            return $next($request);
        }

        // 3. Si aucun tenant courant
        if (!$this->tenantContext->hasTenant()) {
            return $next($request);
        }

        $tenant = $this->tenantContext->tenant();

        // 4. Si PLATFORM_ENFORCE_SUBSCRIPTION_MIDDLEWARE=false
        if (!config('platform.enforce_subscription_middleware', false)) {
            return $next($request);
        }

        // 5. Si tenant active, trial, grace_period, payment_due : laisser passer
        $allowedStatuses = ['active', 'trial', 'grace_period', 'payment_due'];
        if (in_array($tenant->status, $allowedStatuses)) {
            return $next($request);
        }

        // 6. Si tenant suspendu, on redirige vers tenant.pending
        if ($tenant->status === 'suspended') {
            if (!$request->routeIs('tenant.pending')) {
                return redirect()->route('tenant.pending', ['tenant' => $tenant->slug])
                    ->with('error', "Cette boutique est suspendue car l'abonnement a expiré.");
            }
        }

        // 7. Si tenant read_only
        if ($tenant->status === 'read_only') {
            // si la requête est GET, HEAD ou OPTIONS : laisser passer
            if ($request->isMethodSafe()) {
                return $next($request);
            }

            // Retourner une réponse JSON 403 si la requête attend du JSON
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'message' => 'Boutique en lecture seule. Les modifications de données sont impossibles.',
                    'error' => 'Boutique en lecture seule. Les modifications de données sont impossibles.',
                ], 403);
            }

            // sinon bloquer les méthodes d'écriture : POST, PUT, PATCH, DELETE
            return redirect()
                ->back()
                ->with('error', 'Boutique en lecture seule. Les modifications de données sont impossibles.')
                ->withErrors([
                    'subscription' => 'Boutique en lecture seule. Les modifications de données sont impossibles.',
                ]);
        }

        return $next($request);
    }
}
