<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Display the platform landlord overview.
     */
    public function overview()
    {
        $tenantsCount = Tenant::count();
        $plansCount = Plan::count();
        $subscriptionsCount = Subscription::count();
        $paymentsCount = SubscriptionPayment::count();

        // Fetch recent tenants and subscriptions for a better wow factor dashboard
        $recentTenants = Tenant::latest()->limit(5)->get();
        $recentPayments = SubscriptionPayment::with(['tenant', 'subscription'])->latest()->limit(5)->get();

        return view('admin.platform.overview', compact(
            'tenantsCount',
            'plansCount',
            'subscriptionsCount',
            'paymentsCount',
            'recentTenants',
            'recentPayments'
        ));
    }
}
