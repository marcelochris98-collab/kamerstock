<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantResolver;
use App\Services\Tenancy\TenantDatabaseManager;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

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
        if ($this->resolver->shouldIgnoreRequest($request)) {
            return $next($request);
        }

        // 2. Skip if tenant resolution is disabled in configuration
        if (!config('platform.tenant_resolution_enabled', true)) {
            return $next($request);
        }

        $tenantParam = config('platform.tenant_query_parameter', 'tenant');
        $hasTenantParam = $request->has($tenantParam);

        // 3. Attempt to resolve tenant
        $tenant = $this->resolver->resolveFromRequest($request);

        if ($tenant) {
            // 4. Save resolved tenant in context
            $this->context->setTenant($tenant);

            if (config('platform.security.log_tenant_resolution', true)) {
                Log::info('Tenant resolved', [
                    'tenant_id' => $tenant->id,
                    'slug' => $tenant->slug,
                    'status' => $tenant->status,
                    'provisioning_status' => $tenant->provisioning_status,
                ]);
            }

            // 5. Redirect pending/prepared tenants to pending page if tenancy is enabled
            if (config('platform.tenancy_enabled', false)) {
                $pendingStatuses = config('platform.tenant_pending_statuses', ['prepared', 'pending']);
                if (in_array($tenant->provisioning_status, $pendingStatuses, true)) {
                    if (!$request->routeIs('tenant.pending') && !$request->routeIs('tenant.debug')) {
                        return redirect()->route('tenant.pending', ['tenant' => $tenant->slug]);
                    }
                }
            }

            // 6. Configure database connection if tenancy is enabled
            if (config('platform.tenancy_enabled', false)) {
                $this->dbManager->configureForTenant($tenant);
            }
        } else {
            // If tenant parameter is supplied but no tenant resolved, and tenancy is enabled, show 404
            if ($hasTenantParam && config('platform.tenancy_enabled', false)) {
                abort(404, 'Boutique introuvable');
            }
        }

        return $next($request);
    }
}
