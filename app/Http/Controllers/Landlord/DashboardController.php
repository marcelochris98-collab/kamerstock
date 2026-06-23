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

        $activeSupportCount = SupportAccess::where('status', 'approved')
            ->where('ends_at', '>', Carbon::now())
            ->count();

        $recentBackups = TenantBackup::with('tenant')->latest()->limit(5)->get();
        $recentTenants = Tenant::latest()->limit(5)->get();
        $recentPayments = SubscriptionPayment::with('tenant')->latest()->limit(5)->get();

        return view('landlord.dashboard', compact(
            'tenantsCount',
            'activeTenantsCount',
            'suspendedTenantsCount',
            'plansCount',
            'expiringSubscriptionsCount',
            'pendingPaymentsCount',
            'activeSupportCount',
            'recentBackups',
            'recentTenants',
            'recentPayments'
        ));
    }
}
