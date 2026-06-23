<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\TenantBackup;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the landlord super admin dashboard.
     */
    public function index()
    {
        $tenantsCount = Tenant::count();
        $activeTenantsCount = Tenant::where('status', 'active')->count();
        $suspendedTenantsCount = Tenant::where('status', 'suspended')
            ->orWhereNotNull('suspended_at')
            ->count();

        $plansCount = Plan::where('is_active', true)->count();

        // Subscriptions expiring within 7 days
        $expiringSubscriptionsCount = Subscription::where('status', 'active')
            ->where('ends_at', '<=', Carbon::now()->addDays(7))
            ->where('ends_at', '>', Carbon::now())
            ->count();

        $pendingPaymentsCount = SubscriptionPayment::where('status', 'pending')->count();

        $activeSupportCount = SupportAccess::where('status', 'active')
            ->where('starts_at', '<=', Carbon::now())
            ->where('ends_at', '>', Carbon::now())
            ->whereNull('revoked_at')
            ->count();

        // Accès support actifs
        $activeSupportAccesses = SupportAccess::with('tenant')
            ->where('status', 'active')
            ->where('starts_at', '<=', Carbon::now())
            ->where('ends_at', '>', Carbon::now())
            ->whereNull('revoked_at')
            ->orderBy('ends_at')
            ->get();

        // Accès en attente
        $pendingSupportAccesses = SupportAccess::with('tenant')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Accès expirés ou révoqués récemment
        $recentlyExpiredSupportAccesses = SupportAccess::with('tenant')
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

        $recentBackups = TenantBackup::with('tenant')->latest()->limit(5)->get();
        $recentTenants = Tenant::latest()->limit(5)->get();
        $recentPayments = SubscriptionPayment::with('tenant')->latest()->limit(5)->get();

        $completedBackupsCount = TenantBackup::where('status', 'completed')->count();
        $failedBackupsCount = TenantBackup::where('status', 'failed')->count();
        $pendingBackupsCount = TenantBackup::whereIn('status', ['pending', 'running'])->count();
        $lastBackup = TenantBackup::with('tenant')->whereNotNull('finished_at')->latest('finished_at')->first();
        $tenantsWithoutBackupCount = Tenant::whereDoesntHave('backups')->count();

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
}
