<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantDatabaseManager;
use App\Services\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        private TenantResolver $resolver,
        private TenantContext $context,
        private TenantDatabaseManager $databaseManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $this->resolver->shouldIgnoreRequest($request)
            || ! config('platform.tenant_resolution_enabled', true)
            || ! config('platform.tenancy_enabled', true)
        ) {
            return $next($request);
        }

        $tenantParameter = config('platform.tenant_query_parameter', 'tenant');
        $pathPrefix = config('platform.tenant_path_prefix', 't');
        $segments = $request->segments();
        $pathSlug = count($segments) >= 2 && $segments[0] === $pathPrefix
            ? $segments[1]
            : null;
        $requestedSlug = $request->query($tenantParameter) ?? $request->input($tenantParameter) ?? $pathSlug;
        $sessionSlug = $request->session()->get('current_tenant_slug');
        $authenticatedWithoutTenant = ! $sessionSlug && Auth::guard('web')->check();

        if ($sessionSlug && $requestedSlug && $requestedSlug !== $sessionSlug) {
            abort(404, 'Boutique introuvable');
        }

        if ($authenticatedWithoutTenant && $requestedSlug) {
            abort(404, 'Boutique introuvable');
        }

        $tenant = $this->resolver->resolveFromRequest($request);

        if (! $tenant) {
            if ($sessionSlug || $requestedSlug || $pathSlug) {
                abort(404, 'Boutique introuvable');
            }

            return $next($request);
        }

        if ($authenticatedWithoutTenant) {
            abort(404, 'Boutique introuvable');
        }

        if ($tenant->status !== 'active') {
            abort(403, 'Cet espace entreprise n’est pas disponible.');
        }

        $this->context->setTenant($tenant);
        $request->session()->put('current_tenant_slug', $tenant->slug);

        Log::info('Tenant résolu', [
            'tenant_id' => $tenant->id,
            'slug' => $tenant->slug,
        ]);

        $this->databaseManager->configureForTenant($tenant);

        try {
            return $next($request);
        } finally {
            $this->databaseManager->restoreDefaultConnection();
            $this->context->clear();
        }
    }
}
