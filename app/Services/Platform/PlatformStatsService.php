<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\TenantBackup;
use App\Models\Platform\LandlordAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Exception;

class PlatformStatsService
{
    /**
     * Obtenir une vue d'ensemble rapide pour le Dashboard.
     */
    public function overview(): array
    {
        return [
            'tenants_count' => $this->safeCount(Tenant::class),
            'active_tenants_count' => $this->safeCount(Tenant::class, ['status' => 'active']),
            'trial_tenants_count' => $this->safeCount(Tenant::class, ['status' => 'trial']),
            'suspended_tenants_count' => $this->safeQueryCount(function() {
                return Tenant::where('status', 'suspended')->orWhereNotNull('suspended_at')->count();
            }),
            'read_only_tenants_count' => $this->safeQueryCount(function() {
                return Tenant::where('status', 'read_only')->orWhereNotNull('read_only_at')->count();
            }),
            'plans_count' => $this->safeCount(Plan::class, ['is_active' => true]),
            'active_support_count' => $this->safeQueryCount(function() {
                return SupportAccess::where('status', 'active')
                    ->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>', Carbon::now())
                    ->whereNull('revoked_at')
                    ->count();
            }),
            'expiring_subscriptions_count' => $this->safeQueryCount(function() {
                return Subscription::where('status', 'active')
                    ->where('ends_at', '<=', Carbon::now()->addDays(5))
                    ->where('ends_at', '>', Carbon::now())
                    ->count();
            }),
            'pending_payments_count' => $this->safeCount(SubscriptionPayment::class, ['status' => 'pending']),
            'failed_backups_count' => $this->safeCount(TenantBackup::class, ['status' => 'failed']),
            'recent_backups' => $this->safeQueryGet(function() {
                return TenantBackup::with('tenant')->latest()->limit(5)->get();
            }, collect()),
        ];
    }

    /**
     * Statistiques détaillées des boutiques.
     */
    public function tenantStats(): array
    {
        $byStatus = [];
        try {
            if (Schema::connection('landlord')->hasTable('platform_tenants')) {
                $byStatus = Tenant::groupBy('status')
                    ->selectRaw('status, count(*) as count')
                    ->pluck('count', 'status')
                    ->toArray();
            }
        } catch (Exception $e) {}

        return [
            'total' => $this->safeCount(Tenant::class),
            'by_status' => $byStatus,
            'created_this_month' => $this->safeQueryCount(function() {
                return Tenant::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            }),
            'created_today' => $this->safeQueryCount(function() {
                return Tenant::where('created_at', '>=', Carbon::now()->startOfDay())->count();
            }),
        ];
    }

    /**
     * Statistiques détaillées des abonnements.
     */
    public function subscriptionStats(): array
    {
        return [
            'active' => $this->safeCount(Subscription::class, ['status' => 'active']),
            'expired' => $this->safeQueryCount(function() {
                return Subscription::where('status', 'expired')
                    ->orWhere('ends_at', '<=', Carbon::now())
                    ->count();
            }),
            'expiring_5_days' => $this->safeQueryCount(function() {
                return Subscription::where('status', 'active')
                    ->where('ends_at', '<=', Carbon::now()->addDays(5))
                    ->where('ends_at', '>', Carbon::now())
                    ->count();
            }),
            'trial' => $this->safeCount(Subscription::class, ['status' => 'trial']),
            'suspended' => $this->safeCount(Subscription::class, ['status' => 'suspended']),
            'cancelled' => $this->safeCount(Subscription::class, ['status' => 'cancelled']),
        ];
    }

    /**
     * Statistiques des paiements.
     */
    public function paymentStats(): array
    {
        return [
            'paid_count' => $this->safeCount(SubscriptionPayment::class, ['status' => 'paid']),
            'pending_count' => $this->safeCount(SubscriptionPayment::class, ['status' => 'pending']),
            'failed_count' => $this->safeCount(SubscriptionPayment::class, ['status' => 'failed']),
            'total_amount' => $this->safeSum(SubscriptionPayment::class, 'amount', ['status' => 'paid']),
            'amount_this_month' => $this->safeQuerySum(function() {
                return SubscriptionPayment::where('status', 'paid')
                    ->where('paid_at', '>=', Carbon::now()->startOfMonth())
                    ->sum('amount');
            }),
            'amount_today' => $this->safeQuerySum(function() {
                return SubscriptionPayment::where('status', 'paid')
                    ->where('paid_at', '>=', Carbon::now()->startOfDay())
                    ->sum('amount');
            }),
        ];
    }

    /**
     * Statistiques du support technique.
     */
    public function supportStats(): array
    {
        return [
            'active' => $this->safeQueryCount(function() {
                return SupportAccess::where('status', 'active')
                    ->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>', Carbon::now())
                    ->whereNull('revoked_at')
                    ->count();
            }),
            'expired' => $this->safeQueryCount(function() {
                return SupportAccess::where('status', 'expired')
                    ->orWhere('ends_at', '<=', Carbon::now())
                    ->count();
            }),
            'revoked' => $this->safeQueryCount(function() {
                return SupportAccess::where('status', 'revoked')
                    ->orWhereNotNull('revoked_at')
                    ->count();
            }),
            'created_this_month' => $this->safeQueryCount(function() {
                return SupportAccess::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            }),
        ];
    }

    /**
     * Statistiques des sauvegardes.
     */
    public function backupStats(): array
    {
        return [
            'completed' => $this->safeCount(TenantBackup::class, ['status' => 'completed']),
            'failed' => $this->safeCount(TenantBackup::class, ['status' => 'failed']),
            'pending' => $this->safeCount(TenantBackup::class, ['status' => 'pending']),
            'running' => $this->safeCount(TenantBackup::class, ['status' => 'running']),
            'last_backup' => $this->safeQueryGet(function() {
                return TenantBackup::with('tenant')->whereNotNull('finished_at')->latest('finished_at')->first();
            }, null),
            'tenants_without_backup' => $this->safeQueryCount(function() {
                return Tenant::whereDoesntHave('backups')->count();
            }),
        ];
    }

    /**
     * Croissance mensuelle des boutiques.
     */
    public function growthStats(): array
    {
        $growth = [];
        try {
            if (Schema::connection('landlord')->hasTable('platform_tenants')) {
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $count = Tenant::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count();
                    $growth[$month->translatedFormat('M Y')] = $count;
                }
            }
        } catch (Exception $e) {}

        return $growth;
    }

    /**
     * Répartition des boutiques par type de commerce.
     */
    public function businessTypeStats(): array
    {
        try {
            if (Schema::connection('landlord')->hasTable('platform_tenants')) {
                return Tenant::groupBy('business_type')
                    ->selectRaw('business_type, count(*) as count')
                    ->pluck('count', 'business_type')
                    ->toArray();
            }
        } catch (Exception $e) {}

        return [];
    }

    /**
     * Répartition des abonnements par plan.
     */
    public function planDistribution(): array
    {
        try {
            if (Schema::connection('landlord')->hasTable('platform_subscriptions') && Schema::connection('landlord')->hasTable('platform_plans')) {
                return Subscription::join('platform_plans', 'platform_subscriptions.plan_id', '=', 'platform_plans.id')
                    ->where('platform_subscriptions.status', 'active')
                    ->groupBy('platform_plans.name')
                    ->selectRaw('platform_plans.name, count(*) as count')
                    ->pluck('count', 'platform_plans.name')
                    ->toArray();
            }
        } catch (Exception $e) {}

        return [];
    }

    /**
     * Journal d'activité récent (Landlord Audit Logs).
     */
    public function recentActivity()
    {
        return $this->safeQueryGet(function() {
            return LandlordAuditLog::with(['tenant', 'landlordUser'])->latest()->limit(10)->get();
        }, collect());
    }

    /**
     * Résumé de l'état de santé de la plateforme.
     */
    public function healthSummary(): array
    {
        $landlordConnectionOk = false;
        try {
            DB::connection('landlord')->getPdo();
            $landlordConnectionOk = true;
        } catch (Exception $e) {}

        $platformTablesOk = false;
        if ($landlordConnectionOk) {
            try {
                $tables = [
                    'platform_tenants',
                    'platform_plans',
                    'platform_subscriptions',
                    'platform_landlord_users',
                ];
                $allExist = true;
                foreach ($tables as $table) {
                    if (!Schema::connection('landlord')->hasTable($table)) {
                        $allExist = false;
                        break;
                    }
                }
                $platformTablesOk = $allExist;
            } catch (Exception $e) {}
        }

        $legacyTenantExists = false;
        if ($platformTablesOk) {
            try {
                $legacyTenantExists = Tenant::where('provisioning_status', 'legacy_current_db')->exists();
            } catch (Exception $e) {}
        }

        return [
            'landlord_connection_ok' => $landlordConnectionOk,
            'platform_tables_ok' => $platformTablesOk,
            'legacy_tenant_exists' => $legacyTenantExists,
            'tenants_prepared_count' => $this->safeCount(Tenant::class, ['provisioning_status' => 'prepared']),
            'tenants_failed_count' => $this->safeQueryCount(function() {
                return Tenant::whereNotNull('provisioning_error')->count();
            }),
            'failed_backups_count' => $this->safeCount(TenantBackup::class, ['status' => 'failed']),
            'active_support_count' => $this->safeQueryCount(function() {
                return SupportAccess::where('status', 'active')
                    ->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>', Carbon::now())
                    ->whereNull('revoked_at')
                    ->count();
            }),
        ];
    }

    // Helper functions

    private function safeCount(string $modelClass, array $where = []): int
    {
        try {
            $model = new $modelClass;
            $conn = $model->getConnectionName();
            if (Schema::connection($conn)->hasTable($model->getTable())) {
                if (empty($where)) {
                    return $modelClass::count();
                }
                return $modelClass::where($where)->count();
            }
        } catch (Exception $e) {}
        return 0;
    }

    private function safeSum(string $modelClass, string $column, array $where = []): float
    {
        try {
            $model = new $modelClass;
            $conn = $model->getConnectionName();
            if (Schema::connection($conn)->hasTable($model->getTable())) {
                if (empty($where)) {
                    return (float) $modelClass::sum($column);
                }
                return (float) $modelClass::where($where)->sum($column);
            }
        } catch (Exception $e) {}
        return 0.0;
    }

    private function safeQueryCount(callable $callback): int
    {
        try {
            return $callback();
        } catch (Exception $e) {}
        return 0;
    }

    private function safeQuerySum(callable $callback): float
    {
        try {
            return (float) $callback();
        } catch (Exception $e) {}
        return 0.0;
    }

    private function safeQueryGet(callable $callback, $default)
    {
        try {
            return $callback();
        } catch (Exception $e) {}
        return $default;
    }
}
