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
        'provisioning_status',
        'provisioning_error',
        'owner_password_plain',
        'owner_login_email',
        'owner_login_password_generated_at',
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
            'owner_login_password_generated_at' => 'datetime',
            'owner_password_plain' => 'encrypted',
        ];
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']) && !$this->isSuspended();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || !is_null($this->suspended_at);
    }

    public function isReadOnly(): bool
    {
        return $this->status === 'read_only' || !is_null($this->read_only_at);
    }

    public function isProvisioned(): bool
    {
        return in_array($this->provisioning_status, ['database_created', 'migrated']);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
            'trial' => 'bg-indigo-50 text-indigo-750 border-indigo-250',
            'payment_due' => 'bg-amber-50 text-amber-700 border-amber-250',
            'grace_period' => 'bg-orange-50 text-orange-700 border-orange-250',
            'read_only' => 'bg-violet-50 text-violet-700 border-violet-250',
            'suspended' => 'bg-rose-50 text-rose-700 border-rose-250',
            'archived' => 'bg-slate-50 text-slate-700 border-slate-200',
            default => 'bg-slate-50 text-slate-750 border-slate-200',
        };
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
