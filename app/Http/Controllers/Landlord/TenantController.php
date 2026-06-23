<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\LandlordAuditLog;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TenantController extends Controller
{
    /**
     * Display a listing of the tenants.
     */
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10);
        return view('landlord.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('landlord.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landlord.platform_tenants,slug',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_type_custom' => 'nullable|string|max:255',
            'status' => 'required|string|in:trial,active,payment_due,grace_period,read_only,suspended,archived',
            'plan_id' => 'nullable|exists:landlord.platform_plans,id',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'timezone' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
        ]);

        // Clean slug
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['uuid'] = (string) Str::uuid();

        // Default timezone and currency
        $validated['timezone'] = $validated['timezone'] ?? 'Africa/Douala';
        $validated['currency'] = $validated['currency'] ?? 'FCFA';

        $tenant = Tenant::create($validated);

        // If plan is assigned, create initial subscription
        if ($request->filled('plan_id')) {
            $plan = Plan::find($request->plan_id);
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $validated['status'] === 'trial' ? 'trial' : 'active',
                'starts_at' => now(),
                'ends_at' => ($validated['subscription_ends_at'] ?? null) ? Carbon::parse($validated['subscription_ends_at']) : now()->addMonth(),
                'trial_ends_at' => ($validated['trial_ends_at'] ?? null) ? Carbon::parse($validated['trial_ends_at']) : null,
                'amount' => $plan->price_monthly,
                'currency' => $validated['currency'],
                'billing_cycle' => 'monthly',
                'auto_renew' => true,
            ]);
        }

        LandlordAuditLog::log('tenant_create', "Boutique créée : {$tenant->name} (Slug: {$tenant->slug})", $tenant->id);

        return redirect()->route('landlord.tenants.index')->with('success', 'Boutique créée avec succès.');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['subscriptions.plan', 'subscriptionPayments', 'domains', 'backups', 'auditLogs.landlordUser']);
        return view('landlord.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        // Get active subscription plan ID if exists
        $activeSub = $tenant->subscriptions()->whereIn('status', ['trial', 'active'])->latest()->first();
        $currentPlanId = $activeSub ? $activeSub->plan_id : null;

        return view('landlord.tenants.edit', compact('tenant', 'plans', 'currentPlanId'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:landlord.platform_tenants,slug,{$tenant->id}",
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_type_custom' => 'nullable|string|max:255',
            'status' => 'required|string|in:trial,active,payment_due,grace_period,read_only,suspended,archived',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'timezone' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        $tenant->update($validated);

        LandlordAuditLog::log('tenant_update', "Boutique mise à jour : {$tenant->name}", $tenant->id);

        return redirect()->route('landlord.tenants.show', $tenant)->with('success', 'Boutique mise à jour avec succès.');
    }

    /**
     * Suspend the tenant.
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        // Also suspend active subscriptions
        $tenant->subscriptions()->whereIn('status', ['trial', 'active'])->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        LandlordAuditLog::log('tenant_suspend', "Boutique suspendue : {$tenant->name}", $tenant->id);

        return back()->with('success', "La boutique {$tenant->name} a été suspendue.");
    }

    /**
     * Activate the tenant.
     */
    public function activate(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
            'read_only_at' => null,
        ]);

        // Restore subscriptions
        $tenant->subscriptions()->where('status', 'suspended')->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);

        LandlordAuditLog::log('tenant_activate', "Boutique réactivée : {$tenant->name}", $tenant->id);

        return back()->with('success', "La boutique {$tenant->name} a été activée.");
    }

    /**
     * Put the tenant in read-only mode.
     */
    public function readOnly(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'read_only',
            'read_only_at' => now(),
        ]);

        LandlordAuditLog::log('tenant_readonly', "Boutique passée en lecture seule : {$tenant->name}", $tenant->id);

        return back()->with('success', "La boutique {$tenant->name} est maintenant en lecture seule.");
    }
}
