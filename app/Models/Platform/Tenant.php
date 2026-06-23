<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use SoftDeletes;

    protected $connection = 'landlord';
    protected $table = 'platform_tenants';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'owner_name',
        'owner_email',
        'owner_phone',
        'business_type',
        'business_type_custom',
        'status',
        'database_name',
        'database_username',
        'database_password',
        'database_host',
        'database_port',
        'domain',
        'subdomain',
        'logo',
        'timezone',
        'currency',
        'trial_ends_at',
        'subscription_ends_at',
        'suspended_at',
        'read_only_at',
        'last_login_at',
        'settings',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'read_only_at' => 'datetime',
            'last_login_at' => 'datetime',
            'settings' => 'array',
            'database_password' => 'encrypted',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'tenant_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id');
    }

    public function supportAccesses(): HasMany
    {
        return $this->hasMany(SupportAccess::class, 'tenant_id');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(TenantBackup::class, 'tenant_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LandlordAuditLog::class, 'tenant_id');
    }
}
