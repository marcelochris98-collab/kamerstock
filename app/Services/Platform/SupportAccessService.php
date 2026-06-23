<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\LandlordAuditLog;
use App\Services\Platform\LandlordAuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SupportAccessService
{
    /**
     * Create a new support access request in 'pending' state.
     */
    public function createAccess(Tenant $tenant, array $data): SupportAccess
    {
        $access = SupportAccess::create([
            'tenant_id' => $tenant->id,
            'requested_by' => $data['requested_by'] ?? auth('landlord')->id(),
            'granted_to' => $data['granted_to'] ?? auth('landlord')->id(),
            'reason' => $data['reason'] ?? 'Support Technique',
            'status' => 'pending',
            'starts_at' => null,
            'ends_at' => null,
            'metadata' => [
                'duration' => $data['duration'] ?? '30_minutes',
            ],
        ]);

        LandlordAuditService::record(
            'support_access_created',
            $tenant,
            "Demande d'accès support créée pour la boutique : {$tenant->name}. Raison: {$access->reason}"
        );

        return $access;
    }

    /**
     * Activate a support access (sets status to active and calculates ends_at).
     */
    public function activateAccess(SupportAccess $access): SupportAccess
    {
        $duration = $access->metadata['duration'] ?? '30_minutes';
        $startsAt = Carbon::now();
        $endsAt = $this->durationToEndDate($duration);

        $access->update([
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'granted_by' => auth('landlord')->id() ?: $access->granted_by,
        ]);

        LandlordAuditService::record(
            'support_access_activated',
            $access->tenant,
            "Accès support activé pour la boutique : {$access->tenant->name} jusqu'au {$endsAt->format('d/m/Y H:i:s')}"
        );

        return $access;
    }

    /**
     * Revoke a support access immediately.
     */
    public function revokeAccess(SupportAccess $access, ?int $revokedBy = null): SupportAccess
    {
        $access->update([
            'status' => 'revoked',
            'revoked_at' => Carbon::now(),
            'revoked_by' => $revokedBy ?? auth('landlord')->id(),
        ]);

        LandlordAuditService::record(
            'support_access_revoked',
            $access->tenant,
            "Accès support révoqué manuellement pour la boutique : {$access->tenant->name}"
        );

        return $access;
    }

    /**
     * Expire support accesses that have ended.
     */
    public function expireOldAccesses(): int
    {
        $expiredCount = 0;
        $now = Carbon::now();

        $accesses = SupportAccess::where('status', 'active')
            ->where('ends_at', '<', $now)
            ->get();

        foreach ($accesses as $access) {
            $access->update(['status' => 'expired']);
            $expiredCount++;

            // System action or specific log
            Log::info("Accès support expiré automatiquement pour le tenant ID: {$access->tenant_id}");
            
            // Record audit log
            LandlordAuditLog::create([
                'landlord_user_id' => null, // Expired by system command
                'tenant_id' => $access->tenant_id,
                'action' => 'support_access_expired',
                'description' => "Accès support expiré automatiquement (durée dépassée)",
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Console',
            ]);
        }

        return $expiredCount;
    }

    /**
     * Check if support access is authorized for a tenant.
     */
    public function canAccessTenant(Tenant $tenant, $landlordUser = null): bool
    {
        return !is_null($this->activeAccessForTenant($tenant, $landlordUser));
    }

    /**
     * Get the current active support access for a tenant.
     */
    public function activeAccessForTenant(Tenant $tenant, $landlordUser = null): ?SupportAccess
    {
        $userId = $landlordUser ? (is_numeric($landlordUser) ? $landlordUser : $landlordUser->id) : auth('landlord')->id();

        $query = SupportAccess::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('starts_at', '<=', Carbon::now())
            ->where('ends_at', '>', Carbon::now())
            ->whereNull('revoked_at');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('granted_to', $userId)
                  ->orWhereNull('granted_to');
            });
        }

        return $query->first();
    }

    /**
     * Convert duration label to target Carbon end date.
     */
    public function durationToEndDate(string $duration): Carbon
    {
        $now = Carbon::now();

        return match ($duration) {
            '30_minutes', '30m' => $now->addMinutes(30),
            '1_hour', '1h' => $now->addHour(),
            '24_hours', '24h' => $now->addHours(24),
            default => $now->addMinutes(30),
        };
    }

    /**
     * Translates status string into human-friendly French label.
     */
    public function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'active' => 'Actif',
            'expired' => 'Expiré',
            'revoked' => 'Révoqué',
            'denied' => 'Refusé',
            default => $status,
        };
    }

    /**
     * Translates status string into a bootstrap/tailwind CSS badge class.
     */
    public function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
            'expired' => 'bg-slate-100 text-slate-600 border border-slate-200',
            'revoked' => 'bg-rose-50 text-rose-750 border-rose-200',
            'denied' => 'bg-red-50 text-red-750 border-red-200',
            default => 'bg-slate-150 text-slate-700',
        };
    }
}
