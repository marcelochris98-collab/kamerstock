<?php

namespace App\Services\Platform;

use App\Models\Platform\SupportAccess;
use App\Models\Platform\Tenant;

class SupportContext
{
    protected ?SupportAccess $access = null;

    /**
     * Set the current support access.
     */
    public function setAccess(?SupportAccess $access): void
    {
        $this->access = $access;
    }

    /**
     * Get the current support access.
     */
    public function access(): ?SupportAccess
    {
        return $this->access;
    }

    /**
     * Check if support access is active.
     */
    public function hasAccess(): bool
    {
        return !is_null($this->access);
    }

    /**
     * Get the tenant linked to current support access.
     */
    public function tenant(): ?Tenant
    {
        return $this->access?->tenant;
    }

    /**
     * Clear the support context.
     */
    public function clear(): void
    {
        $this->access = null;
    }

    /**
     * Determine if currently in support mode.
     */
    public function isSupportMode(): bool
    {
        return $this->hasAccess() && $this->access->canBeUsed();
    }
}
