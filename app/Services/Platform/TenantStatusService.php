<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use Carbon\Carbon;

class TenantStatusService
{
    /**
     * Check if the tenant is in normal active status (trial or active subscription).
     */
    public function isActive(Tenant $tenant): bool
    {
        $status = $this->determineStatus($tenant);
        return in_array($status, ['active', 'trial', 'grace_period']);
    }

    /**
     * Check if the tenant has read-only access.
     */
    public function isReadOnly(Tenant $tenant): bool
    {
        $status = $this->determineStatus($tenant);
        return $status === 'read_only' || $tenant->status === 'read_only' || !is_null($tenant->read_only_at);
    }

    /**
     * Check if the tenant is suspended.
     */
    public function isSuspended(Tenant $tenant): bool
    {
        $status = $this->determineStatus($tenant);
        return $status === 'suspended' || $tenant->status === 'suspended' || !is_null($tenant->suspended_at);
    }

    /**
     * Calculate days remaining until trial or subscription ends.
     */
    public function daysBeforeExpiration(Tenant $tenant): int
    {
        $endDate = $this->getExpirationDate($tenant);
        if (!$endDate) {
            return 0;
        }

        return (int) Carbon::now()->diffInDays($endDate, false);
    }

    /**
     * Get the expiration date (either trial ends at or subscription ends at).
     */
    public function getExpirationDate(Tenant $tenant): ?Carbon
    {
        if ($tenant->subscription_ends_at) {
            return Carbon::parse($tenant->subscription_ends_at);
        }

        if ($tenant->trial_ends_at) {
            return Carbon::parse($tenant->trial_ends_at);
        }

        return null;
    }

    /**
     * Determine the tenant's status dynamically based on dates and thresholds.
     */
    public function determineStatus(Tenant $tenant): string
    {
        // Explicitly suspended or archived takes precedence
        if ($tenant->status === 'suspended' || !is_null($tenant->suspended_at)) {
            return 'suspended';
        }

        if ($tenant->status === 'archived') {
            return 'archived';
        }

        if ($tenant->status === 'read_only' || !is_null($tenant->read_only_at)) {
            return 'read_only';
        }

        $endDate = $this->getExpirationDate($tenant);
        if (!$endDate) {
            return $tenant->status ?: 'trial';
        }

        $now = Carbon::now();
        
        // If not expired yet
        if ($endDate->isFuture()) {
            if ($tenant->subscription_ends_at) {
                return 'active';
            }
            return 'trial';
        }

        // It is expired, check thresholds
        $daysPast = (int) $endDate->diffInDays($now, false);
        $gracePeriod = config('platform.grace_period_days', 5);
        $readOnlyDays = config('platform.read_only_after_days', 6);
        $suspensionDays = config('platform.suspension_after_days', 11);

        if ($daysPast <= $gracePeriod) {
            return 'grace_period';
        }

        if ($daysPast <= $readOnlyDays) {
            return 'payment_due';
        }

        if ($daysPast <= $suspensionDays) {
            return 'read_only';
        }

        return 'suspended';
    }
}
