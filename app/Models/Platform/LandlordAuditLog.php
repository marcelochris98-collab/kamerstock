<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordAuditLog extends Model
{
    protected $connection = 'landlord';
    protected $table = 'platform_landlord_audit_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'landlord_user_id',
        'tenant_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function landlordUser(): BelongsTo
    {
        return $this->belongsTo(LandlordUser::class, 'landlord_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public static function log(string $action, ?string $description = null, ?int $tenantId = null, ?array $metadata = null): void
    {
        self::create([
            'landlord_user_id' => auth('landlord')->id(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
