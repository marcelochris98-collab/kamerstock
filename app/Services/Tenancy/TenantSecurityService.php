<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Http\Request;

class TenantSecurityService
{
    /**
     * Determine if the request targets a landlord route.
     */
    public function isLandlordRoute(Request $request): bool
    {
        return $request->is('landlord/*') || $request->is('landlord');
    }

    /**
     * Determine if the request targets a tenant/boutique route.
     */
    public function isTenantRoute(Request $request): bool
    {
        return !$this->isLandlordRoute($request) && !$this->isStaticOrTechnical($request);
    }

    /**
     * Determine if it's safe to resolve a tenant for the request.
     */
    public function isSafeToResolveTenant(Request $request): bool
    {
        return !$this->isLandlordRoute($request);
    }

    /**
     * Check if the request is for static or technical files.
     */
    protected function isStaticOrTechnical(Request $request): bool
    {
        $path = $request->path();
        return $request->is('assets/*') || 
               $request->is('build/*') || 
               $request->is('storage/*') || 
               $path === 'favicon.ico' || 
               $path === 'robots.txt';
    }

    /**
     * List of all sensitive fields that should never leak.
     */
    public function sensitiveFields(): array
    {
        return [
            'database_password',
            'owner_password_plain',
            'password',
            'remember_token',
            'api_token',
            'secret',
            'token',
        ];
    }

    /**
     * Sanitize tenant attributes for display/debug purposes.
     */
    public function sanitizeTenantForDisplay(?Tenant $tenant): array
    {
        if (!$tenant) {
            return [];
        }

        $data = $tenant->toArray();

        return $this->assertNoSensitiveFields($data);
    }

    /**
     * Assert and filter any sensitive fields from the data array.
     */
    public function assertNoSensitiveFields(array $data): array
    {
        $sensitive = $this->sensitiveFields();

        foreach ($sensitive as $field) {
            if (array_key_exists($field, $data)) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
