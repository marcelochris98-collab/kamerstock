<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class TenantSecurityService
{
    public function bindAuthenticatedUser(Request $request, Tenant $tenant, Authenticatable $user): void
    {
        $request->session()->put('current_tenant_slug', $tenant->slug);
        $request->session()->put('current_tenant_id', $tenant->id);
        $request->session()->put('current_tenant_user_id', $user->getAuthIdentifier());
    }

    public function validateAuthenticatedTenantSession(Request $request, Authenticatable $user): bool
    {
        $tenantSlug = $request->session()->get('current_tenant_slug');
        $tenantId = $request->session()->get('current_tenant_id');
        $tenantUserId = $request->session()->get('current_tenant_user_id');

        if (! is_string($tenantSlug) || $tenantSlug === '') {
            return app(TenantContext::class)->tenant() === null;
        }

        if ($tenantId === null) {
            $request->session()->put('current_tenant_id', app(TenantContext::class)->tenant()?->id);
            $tenantId = $request->session()->get('current_tenant_id');
        }

        if ($tenantUserId === null) {
            $request->session()->put('current_tenant_user_id', $user->getAuthIdentifier());
            $tenantUserId = $user->getAuthIdentifier();
        }

        return (int) $tenantUserId === (int) $user->getAuthIdentifier()
            && $tenantId !== null;
    }

    public function forgetTenantBinding(Request $request): void
    {
        $request->session()->forget([
            'current_tenant_slug',
            'current_tenant_id',
            'current_tenant_user_id',
        ]);
    }
}
