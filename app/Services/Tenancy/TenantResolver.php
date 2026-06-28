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

        try {
            // Strategy 1: Path prefix (/t/boutique-test/...)
            $tenant = $this->resolveFromPath($request);
            if ($tenant) return $tenant;

            // Strategy 2: Query parameter (?tenant=boutique-test)
            $tenant = $this->resolveFromQuery($request);
            if ($tenant) return $tenant;

            // ✅ Strategy 3: Session (current_tenant_slug sauvegardé au login)
            // Permet de maintenir le contexte tenant sur toutes les requêtes
            // sans avoir besoin du ?tenant= dans chaque URL.
            $tenant = $this->resolveFromSession($request);
            if ($tenant) return $tenant;

            // Strategy 4: Subdomain / Custom Domain
            $tenant = $this->resolveFromSubdomain($request);
            if ($tenant) return $tenant;

        } catch (\Throwable $e) {
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
            $request->is('css/*') || $request->is('js/*') || $request->is('images/*') ||
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
     * ✅ Strategy 3 : Résolution depuis la session Laravel.
     * Le slug est sauvegardé dans session('current_tenant_slug') au moment du login.
     * Cela évite de devoir passer ?tenant= dans chaque URL après connexion.
     */
    public function resolveFromSession(Request $request): ?Tenant
    {
        // Ne pas résoudre depuis la session sur les routes de login/logout
        if ($request->routeIs('login') || $request->routeIs('logout')) {
            return null;
        }

        $slug = session('current_tenant_slug');

        if ($slug && is_string($slug)) {
            return $this->resolveBySlug($slug);
        }

        return null;
    }

    /**
     * Resolve tenant using query parameter (?tenant=slug).
     */
    public function resolveFromQuery(Request $request): ?Tenant
    {
        $param = config('platform.tenant_query_parameter', 'tenant');
        $slug = $request->query($param);
        if (!$slug) {
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

            if (Str::endsWith($host, '.' . $centralDomain)) {
                $subdomain = Str::before($host, '.' . $centralDomain);
                if (!Str::contains($subdomain, '.')) {
                    $tenant = $this->resolveBySlug($subdomain);
                    if ($tenant) {
                        return $tenant;
                    }
                }
            }
        }

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