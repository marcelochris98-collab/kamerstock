<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;

class TenantContext
{
    /**
     * The current resolved tenant.
     */
    protected ?Tenant $tenant = null;

    /**
     * Set the current tenant.
     */
    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current tenant.
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Check if a tenant is currently resolved.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->tenant);
    }

    /**
     * Get the ID of the current tenant.
     */
    public function id(): ?int
    {
        return $this->tenant ? $this->tenant->id : null;
    }

    /**
     * Get the slug of the current tenant.
     */
    public function slug(): ?string
    {
        return $this->tenant ? $this->tenant->slug : null;
    }

    /**
     * Check if the current tenant uses the legacy default database.
     */
    public function isLegacyCurrentDatabase(): bool
    {
        return $this->tenant && $this->tenant->provisioning_status === config('platform.legacy_current_database_status', 'legacy_current_db');
    }

    /**
     * Clear the resolved tenant from context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }
}
