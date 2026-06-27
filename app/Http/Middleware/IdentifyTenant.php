<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantResolver;
use App\Services\Tenancy\TenantDatabaseManager;
use App\Services\Platform\TenantStatusService;
use App\Services\Platform\SupportContext;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class IdentifyTenant
{
    protected TenantResolver $resolver;
    protected TenantContext $context;
    protected TenantDatabaseManager $dbManager;
    protected TenantStatusService $statusService;

    public function __construct(
        TenantResolver $resolver,
        TenantContext $context,
        TenantDatabaseManager $dbManager,
        TenantStatusService $statusService
    ) {
        $this->resolver = $resolver;
        $this->context = $context;
        $this->dbManager = $dbManager;
        $this->statusService = $statusService;
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

        $tenant = $this->resolver->resolveFromRequest($request);

        try {
            if ($tenant) {
                $this->context->setTenant($tenant);

                if (config('platform.security.log_tenant_resolution', true)) {
                    Log::info('Tenant résolu', [
                        'tenant_id' => $tenant->id,
                        'slug' => $tenant->slug,
                        'status' => $tenant->status,
                        'provisioning_status' => $tenant->provisioning_status,
                    ]);
                }

                if (config('platform.tenancy_enabled', false)) {
                    if ($this->statusService->isSuspended($tenant)) {
                        if (!$request->routeIs('tenant.pending') && !$request->routeIs('tenant.debug')) {
                            return redirect()->route('tenant.pending', ['tenant' => $tenant->slug])
                                ->with('error', "Cette boutique est suspendue car l'abonnement a expiré.");
                        }
                    }
                }

                if (config('platform.tenancy_enabled', false)) {
                    $pendingStatuses = config('platform.tenant_pending_statuses', ['prepared', 'pending']);
                    if (in_array($tenant->provisioning_status, $pendingStatuses, true)) {
                        if (!$request->routeIs('tenant.pending') && !$request->routeIs('tenant.debug')) {
                            return redirect()->route('tenant.pending', ['tenant' => $tenant->slug]);
                        }
                    }
                }

                if (config('platform.tenancy_enabled', false)) {
                    if ($this->statusService->isReadOnly($tenant)) {
                        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                            if (!app(SupportContext::class)->isSupportMode()) {
                                if ($request->expectsJson() || $request->wantsJson()) {
                                    return response()->json([
                                        'error' => "Boutique en lecture seule. Modifications impossibles car l'abonnement a expiré."
                                    ], 403);
                                }

                                return back()->with('error', "Boutique en lecture seule. Les modifications de données sont impossibles.");
                            }
                        }
                    }
                }

                if (config('platform.tenancy_enabled', false)) {
                    $this->dbManager->configureForTenant($tenant);
                }
            } else {
                if ($hasTenantParam && config('platform.tenancy_enabled', false)) {
                    abort(404, 'Boutique introuvable');
                }

                if (config('platform.tenancy_enabled', false)) {
                    $this->dbManager->switchToDefault();
                }
            }

            $response = $next($request);

            if (method_exists($response, 'render')) {
                $response->render();
            }

            return $response;
        } finally {
            if (config('platform.tenancy_enabled', false)) {
                $this->dbManager->switchToDefault();
            }
        }
    }
}

