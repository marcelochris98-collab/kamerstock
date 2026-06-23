<?php

namespace App\Models\Platform;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandlordUser extends Authenticatable
{
    use SoftDeletes;

    protected $connection = 'landlord';
    protected $table = 'platform_landlord_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'settings',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LandlordAuditLog::class, 'landlord_user_id');
    }
}
