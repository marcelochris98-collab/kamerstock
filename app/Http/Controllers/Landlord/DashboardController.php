<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\TenantBackup;
use App\Services\Platform\PlatformStatsService;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Exception;

class DashboardController extends Controller
{
    protected PlatformStatsService $statsService;

    public function __construct(PlatformStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Display the landlord super admin dashboard.
     */
    public function index()
    {
        // Utilisation de try-catch et de requêtes sécurisées pour éviter les plantages
        $overview = $this->statsService->overview();
        $backupStats = $this->statsService->backupStats();

        $tenantsCount = $overview['tenants_count'];
        $activeTenantsCount = $overview['active_tenants_count'];
        $suspendedTenantsCount = $overview['suspended_tenants_count'];
        $plansCount = $overview['plans_count'];
        $expiringSubscriptionsCount = $overview['expiring_subscriptions_count'];
        $pendingPaymentsCount = $overview['pending_payments_count'];
        $activeSupportCount = $overview['active_support_count'];

        // Accès support actifs
        $activeSupportAccesses = $this->safeGet(function () {
            return SupportAccess::with('tenant')
                ->where('status', 'active')
                ->where('starts_at', '<=', Carbon::now())
                ->where('ends_at', '>', Carbon::now())
                ->whereNull('revoked_at')
                ->orderBy('ends_at')
                ->get();
        }, collect());

        // Accès en attente
        $pendingSupportAccesses = $this->safeGet(function () {
            return SupportAccess::with('tenant')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }, collect());

        // Accès expirés ou révoqués récemment
        $recentlyExpiredSupportAccesses = $this->safeGet(function () {
            return SupportAccess::with('tenant')
                ->where(function ($query) {
                    $query->where('status', 'expired')
                        ->orWhere('status', 'revoked')
                        ->orWhere(function ($q) {
                            $q->where('status', 'active')
                              ->where('ends_at', '<=', Carbon::now());
                        });
                })
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        }, collect());

        $recentBackups = $overview['recent_backups'];
        
        $recentTenants = $this->safeGet(function () {
            return Tenant::latest()->limit(5)->get();
        }, collect());

        $recentPayments = $this->safeGet(function () {
            return SubscriptionPayment::with('tenant')->latest()->limit(5)->get();
        }, collect());

        $completedBackupsCount = $backupStats['completed'];
        $failedBackupsCount = $backupStats['failed'];
        $pendingBackupsCount = $backupStats['pending'] + $backupStats['running'];
        $lastBackup = $backupStats['last_backup'];
        $tenantsWithoutBackupCount = $backupStats['tenants_without_backup'];

        return view('landlord.dashboard', compact(
            'tenantsCount',
            'activeTenantsCount',
            'suspendedTenantsCount',
            'plansCount',
            'expiringSubscriptionsCount',
            'pendingPaymentsCount',
            'activeSupportCount',
            'activeSupportAccesses',
            'pendingSupportAccesses',
            'recentlyExpiredSupportAccesses',
            'recentBackups',
            'recentTenants',
            'recentPayments',
            'completedBackupsCount',
            'failedBackupsCount',
            'pendingBackupsCount',
            'lastBackup',
            'tenantsWithoutBackupCount'
        ));
    }

    /**
     * Exécute une requête de façon sécurisée et retourne une valeur par défaut en cas d'erreur.
     */
    private function safeGet(callable $callback, $default)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            return $default;
        }
    }
}

