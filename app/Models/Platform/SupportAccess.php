<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportAccess extends Model
{
    protected $connection = 'landlord';
    protected $table = 'platform_support_accesses';

    protected $fillable = [
        'tenant_id',
        'requested_by',
        'granted_by',
        'granted_to',
        'reason',
        'status',
        'starts_at',
        'ends_at',
        'revoked_at',
        'revoked_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
