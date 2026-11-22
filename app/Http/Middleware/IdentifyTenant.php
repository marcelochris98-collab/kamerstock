<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantDatabaseManager;
use App\Services\Tenancy\TenantResolver;
use App\Services\Tenancy\TenantSecurityService;
use App\Services\Tenancy\TenantStatusService;
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
        private TenantDatabaseManager $databaseManager,
        private TenantStatusService $statusService,
        private TenantSecurityService $securityService
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

        if ($sessionSlug) {
            $boundTenantId = $request->session()->get('current_tenant_id');
            if ($boundTenantId !== null) {
                $tenantBySession = $this->resolver->resolveBySlug((string) $sessionSlug);
                if (! $tenantBySession || (int) $boundTenantId !== (int) $tenantBySession->id) {
                    $this->securityService->forgetTenantBinding($request);
                    abort(403, 'La session ne correspond pas à cet espace entreprise.');
                }
            }
        }

        if (Auth::guard('web')->check() && $sessionSlug) {
            $boundUserId = $request->session()->get('current_tenant_user_id');
            if ($boundUserId !== null && (int) $boundUserId !== (int) Auth::id()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                abort(403, 'Cette session utilisateur ne correspond pas à cet espace entreprise.');
            }
        }

        if ($request->routeIs('login') && $requestedSlug && ! $sessionSlug) {
            $requestedTenant = $this->resolver->resolveBySlug((string) $requestedSlug);
            if (! $requestedTenant) {
                abort(404, 'Boutique introuvable');
            }
        }

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

        if (! $this->statusService->canAccess($tenant)) {
            abort(403, $this->statusService->blockedMessage($tenant));
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
