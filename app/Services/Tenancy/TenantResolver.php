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
    /**
     * Resolve tenant from request.
     */
    public function resolveFromRequest(Request $request): ?Tenant
    {
        if ($this->shouldIgnoreRequest($request)) {
            return null;
        }

        try {
            // 2. Resolve using Strategy 1: Path local (/t/boutique-test/...)
            $tenant = $this->resolveFromPath($request);
            if ($tenant) {
                return $tenant;
            }

            // 3. Resolve using Strategy 2: Query parameter (?tenant=boutique-test)
            $tenant = $this->resolveFromQuery($request);
            if ($tenant) {
                return $tenant;
            }

            // 4. Resolve using Strategy 3 & 4: Subdomain / Custom Domain
            $tenant = $this->resolveFromSubdomain($request);
            if ($tenant) {
                return $tenant;
            }
        } catch (\Throwable $e) {
            // Silence resolution errors (e.g. table platform_tenants not migrated yet in some tests)
            if (config('platform.security.log_tenant_resolution', true)) {
                \Illuminate\Support\Facades\Log::warning("Erreur lors de la résolution du tenant: " . $e->getMessage());
            }
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
            $path === 'favicon.ico' || $path === 'robots.txt') {
            return true;
        }

        // tenant-debug only in local/testing
        if ($request->is('tenant-debug') && !app()->environment('local', 'testing')) {
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

        // Check if route starts with /t/{slug}
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

        // If host is a central domain directly, do not resolve subdomain
        if (in_array($host, $centralDomains, true)) {
            return null;
        }

        // Extract subdomain by checking if host ends with one of the central domains
        foreach ($centralDomains as $centralDomain) {
            // Skip IP addresses for subdomain pattern matching
            if (filter_var($centralDomain, FILTER_VALIDATE_IP)) {
                continue;
            }

            if (Str::endsWith($host, '.' . $centralDomain)) {
                $subdomain = Str::before($host, '.' . $centralDomain);
                // Subdomain should not contain dots
                if (!Str::contains($subdomain, '.')) {
                    $tenant = $this->resolveBySlug($subdomain);
                    if ($tenant) {
                        return $tenant;
                    }
                }
            }
        }

        // If not a central subdomain, it might be a custom domain mapped directly
        return $this->resolveByDomain($host);
    }

    /**
     * Resolve tenant by slug (always on 'landlord' connection).
     */
    public function resolveBySlug(string $slug): ?Tenant
    {
        return Tenant::on('landlord')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Resolve tenant by domain (always on 'landlord' connection).
     */
    public function resolveByDomain(string $domain): ?Tenant
    {
        return Tenant::on('landlord')
            ->where('domain', $domain)
            ->first();
    }
}
