<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBackup extends Model
{
    protected $connection = 'landlord';
    protected $table = 'platform_tenant_backups';

    protected $fillable = [
        'tenant_id',
        'filename',
        'path',
        'disk',
        'size_bytes',
        'status',
        'backup_type',
        'started_at',
        'finished_at',
        'downloaded_at',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
