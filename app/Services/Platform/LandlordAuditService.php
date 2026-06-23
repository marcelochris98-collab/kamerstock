<?php

namespace App\Services\Platform;

use App\Models\Platform\LandlordAuditLog;
use App\Models\Platform\Tenant;

class LandlordAuditService
{
    /**
     * Record an audit log entry for landlord super admin actions.
     */
    public static function record(string $action, ?Tenant $tenant = null, ?string $description = null, array $metadata = []): void
    {
        LandlordAuditLog::create([
            'landlord_user_id' => auth('landlord')->id(),
            'tenant_id' => $tenant?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
