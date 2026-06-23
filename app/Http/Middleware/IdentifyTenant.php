<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantResolver;
use App\Services\Tenancy\TenantDatabaseManager;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    protected TenantResolver $resolver;
    protected TenantContext $context;
    protected TenantDatabaseManager $dbManager;

    public function __construct(TenantResolver $resolver, TenantContext $context, TenantDatabaseManager $dbManager)
    {
        $this->resolver = $resolver;
        $this->context = $context;
        $this->dbManager = $dbManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip if request is landlord, static assets or system paths
        if ($request->is('landlord/*') || $request->is('landlord') ||
            $request->is('assets/*') || $request->is('build/*') ||
            $request->is('storage/*') || $request->path() === 'favicon.ico') {
            return $next($request);
        }

        // 2. Skip if tenant resolution is disabled in configuration
        if (!config('platform.tenant_resolution_enabled', true)) {
            return $next($request);
        }

        // 3. Attempt to resolve tenant
        $tenant = $this->resolver->resolveFromRequest($request);

        if ($tenant) {
            // 4. Save resolved tenant in context
            $this->context->setTenant($tenant);

            // 5. Redirect pending/prepared tenants to pending page
            $pendingStatuses = config('platform.tenant_pending_statuses', ['prepared', 'pending']);
            if (in_array($tenant->provisioning_status, $pendingStatuses, true)) {
                if (!$request->routeIs('tenant.pending') && !$request->routeIs('tenant.debug')) {
                    return redirect()->route('tenant.pending', ['tenant' => $tenant->slug]);
                }
            }

            // 6. Configure database connection if tenancy is enabled
            if (config('platform.tenancy_enabled', false)) {
                $this->dbManager->configureForTenant($tenant);
            }
        }

        return $next($request);
    }
}
