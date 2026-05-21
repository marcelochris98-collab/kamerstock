<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'is_active', 'last_login'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login'        => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relations ──────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ── Helpers permissions ────────────────────────────────

    public function hasPermission(string $slug): bool
    {
        if (!$this->role) return false;
        return $this->role->permissions()->where('slug', $slug)->exists();
    }

    public function can($ability, $arguments = []): bool
    {
        // Vérifier d'abord dans les permissions RBAC
        if (is_string($ability)) {
            return $this->hasPermission($ability);
        }
        return parent::can($ability, $arguments);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Label rôle
    public function getRoleLabelAttribute(): string
    {
        return $this->role?->name ?? 'Sans rôle';
    }
}