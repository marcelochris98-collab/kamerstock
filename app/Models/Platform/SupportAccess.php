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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isDenied(): bool
    {
        return $this->status === 'denied';
    }

    public function hasExpired(): bool
    {
        if (!$this->ends_at) {
            return false;
        }
        return $this->ends_at->isPast();
    }

    public function canBeUsed(): bool
    {
        return $this->isActive() &&
            $this->starts_at && $this->starts_at->isPast() &&
            $this->ends_at && $this->ends_at->isFuture() &&
            is_null($this->revoked_at);
    }

    public function durationLabel(): string
    {
        $starts = $this->starts_at;
        $ends = $this->ends_at;
        if (!$starts || !$ends) {
            return 'Non définie';
        }
        $diffInMinutes = $starts->diffInMinutes($ends);
        if ($diffInMinutes < 60) {
            return "{$diffInMinutes} minutes";
        }
        $diffInHours = $starts->diffInHours($ends);
        if ($diffInHours < 24) {
            return "{$diffInHours} heure" . ($diffInHours > 1 ? 's' : '');
        }
        $diffInDays = $starts->diffInDays($ends);
        return "{$diffInDays} jour" . ($diffInDays > 1 ? 's' : '');
    }

    public function remainingDurationLabel(): string
    {
        $ends = $this->ends_at;
        if (!$ends || $ends->isPast() || !is_null($this->revoked_at)) {
            return 'Expiré';
        }
        
        $now = now();
        $diffInMinutes = $now->diffInMinutes($ends);
        if ($diffInMinutes < 60) {
            return "{$diffInMinutes} min" . ($diffInMinutes > 1 ? 's' : '');
        }
        $diffInHours = $now->diffInHours($ends);
        if ($diffInHours < 24) {
            $remainingMinutes = $diffInMinutes % 60;
            if ($remainingMinutes > 0) {
                return "{$diffInHours} h {$remainingMinutes} min";
            }
            return "{$diffInHours} h";
        }
        $diffInDays = $now->diffInDays($ends);
        return "{$diffInDays} j" . ($diffInDays > 1 ? 's' : '');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'active' => 'Actif',
            'expired' => 'Expiré',
            'revoked' => 'Révoqué',
            'denied' => 'Refusé',
            default => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
            'expired' => 'bg-slate-100 text-slate-600 border-slate-200',
            'revoked' => 'bg-rose-50 text-rose-750 border-rose-200',
            'denied' => 'bg-red-50 text-red-750 border-red-200',
            default => 'bg-slate-150 text-slate-700',
        };
    }
}
