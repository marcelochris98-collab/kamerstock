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
        'created_by',
        'checksum',
        'database_name',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'metadata' => 'array',
            'created_by' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'running' => 'En cours',
            'completed' => 'Terminée',
            'failed' => 'Échouée',
            default => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'running' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'failed' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function backupTypeLabel(): string
    {
        return match ($this->backup_type) {
            'manual' => 'Manuelle',
            'automatic' => 'Automatique',
            'pre_migration' => 'Pré-migration',
            default => $this->backup_type,
        };
    }

    public function sizeForHumans(): string
    {
        if (is_null($this->size_bytes)) {
            return '0 o';
        }
        $bytes = $this->size_bytes;
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
