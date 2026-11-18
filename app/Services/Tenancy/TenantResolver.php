<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantResolver
{
    /**
     * Resolve tenant from request.
     */
    public function resolveFromRequest(Request $request): ?Tenant
    {
        if ($this->shouldIgnoreRequest($request)) {
            return null;
        }

        // The session is authoritative after a user has entered a tenant.
        $sessionSlug = session('current_tenant_slug');
        if (is_string($sessionSlug) && $sessionSlug !== '') {
            return $this->resolveBySlug($sessionSlug);
        }

        // Strategy 1: Path prefix (/t/boutique-test/...)
        $tenant = $this->resolveFromPath($request);
        if ($tenant) {
            return $tenant;
        }

        // Strategy 2: Query parameter (?tenant=boutique-test)
        $tenant = $this->resolveFromQuery($request);
        if ($tenant) {
            return $tenant;
        }

        // Strategy 3: Tenant subdomain
        $tenant = $this->resolveFromSubdomain($request);
        if ($tenant) {
            return $tenant;
        }

        return null;
    }

    /**
     * Check if request should be ignored for tenant resolution.
     */
    public function shouldIgnoreRequest(Request $request): bool
    {
        $path = $request->path();

        // landlord routes
        if ($request->is('landlord/*') || $request->is('landlord')) {
            return true;
        }

        // static files
        if ($request->is('assets/*') || $request->is('build/*') || $request->is('storage/*') ||
            $request->is('css/*') || $request->is('js/*') || $request->is('images/*') ||
            $path === 'favicon.ico' || $path === 'robots.txt') {
            return true;
        }

        // tenant-debug only in local/testing
        if ($request->is('tenant-debug') && ! app()->environment('local', 'testing')) {
            return true;
        }

        return false;
    }

    /**
     * Resolve tenant using query parameter (?tenant=slug).
     */
    public function resolveFromQuery(Request $request): ?Tenant
    {
        $param = config('platform.tenant_query_parameter', 'tenant');
        $slug = $request->query($param);
        if (! $slug) {
            $slug = $request->input($param);
        }

        if ($slug && is_string($slug)) {
            return $this->resolveBySlug($slug);
        }

        return null;
    }

    /**
     * Resolve tenant using path prefix (/t/{slug}/...).
     */
    public function resolveFromPath(Request $request): ?Tenant
    {
        $prefix = config('platform.tenant_path_prefix', 't');
        $segments = $request->segments();

        if (count($segments) >= 2 && $segments[0] === $prefix) {
            $slug = $segments[1];

            return $this->resolveBySlug($slug);
        }

        return null;
    }

    /**
     * Resolve tenant using subdomain (e.g., boutique.localhost or custom domain).
     */
    public function resolveFromSubdomain(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $centralDomains = config('platform.central_domains', ['localhost', '127.0.0.1']);

        if (in_array($host, $centralDomains, true)) {
            return null;
        }

        foreach ($centralDomains as $centralDomain) {
            if (filter_var($centralDomain, FILTER_VALIDATE_IP)) {
                continue;
            }

            if (Str::endsWith($host, '.'.$centralDomain)) {
                $subdomain = Str::before($host, '.'.$centralDomain);
                if (! Str::contains($subdomain, '.')) {
                    $tenant = $this->resolveBySlug($subdomain);
                    if ($tenant) {
                        return $tenant;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolve tenant by slug (always on the landlord connection).
     */
    public function resolveBySlug(string $slug): ?Tenant
    {
        return Tenant::on('landlord')
            ->where('slug', $slug)
            ->first();
    }
}
