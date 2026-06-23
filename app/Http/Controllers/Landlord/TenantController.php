<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\LandlordAuditService;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class TenantController extends Controller
{
    protected $provisioningService;

    public function __construct(TenantProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

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
            'slug' => 'nullable|string|max:255|unique:landlord.platform_tenants,slug',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_type_custom' => 'nullable|string|max:255',
            'status' => 'required|string|in:trial,active,payment_due,grace_period,read_only,suspended,archived',
            'plan_id' => 'nullable|exists:landlord.platform_plans,id',
            'trial_days' => 'nullable|integer|min:0',
            'subscription_months' => 'nullable|integer|min:1',
            'subscription_ends_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'subdomain' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'database_name' => 'nullable|string|max:255',
        ]);

        // Create the tenant using service
        $tenant = $this->provisioningService->createTenant($validated);

        // If plan is assigned, create initial subscription
        if ($request->filled('plan_id')) {
            $plan = Plan::find($request->plan_id);
            $this->provisioningService->createInitialSubscription($tenant, $plan, $validated);
        }

        // Run database provisioning (Mode A / Mode B handled internally)
        $this->provisioningService->provision($tenant);

        // Audit the action
        LandlordAuditService::record(
            'tenant_create',
            $tenant,
            "Boutique créée et configurée : {$tenant->name} (Statut provisionnement: {$tenant->provisioning_status})"
        );

        return redirect()->route('landlord.tenants.show', $tenant)->with([
            'success' => 'Boutique créée avec succès.',
            'show_credentials' => true,
        ]);
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

        LandlordAuditService::record('tenant_update', $tenant, "Boutique mise à jour : {$tenant->name}");

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

        $tenant->subscriptions()->whereIn('status', ['trial', 'active'])->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        LandlordAuditService::record('tenant_suspend', $tenant, "Boutique suspendue : {$tenant->name}");

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

        $tenant->subscriptions()->where('status', 'suspended')->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);

        LandlordAuditService::record('tenant_activate', $tenant, "Boutique réactivée : {$tenant->name}");

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

        LandlordAuditService::record('tenant_readonly', $tenant, "Boutique passée en lecture seule : {$tenant->name}");

        return back()->with('success', "La boutique {$tenant->name} est maintenant en lecture seule.");
    }

    /**
     * Regenerate owner password.
     */
    public function regenerateOwnerPassword(Tenant $tenant)
    {
        $passLen = config('platform.tenant_default_password_length', 10);
        $newPassword = $this->provisioningService->generateTemporaryPassword($passLen);

        $tenant->update([
            'owner_password_plain' => $newPassword,
            'owner_login_password_generated_at' => now(),
        ]);

        if ($tenant->provisioning_status === 'migrated') {
            try {
                $dbName = $tenant->database_name;
                $driver = DB::connection('landlord')->getDriverName();

                config([
                    'database.connections.tenant' => [
                        'driver' => $driver,
                        'database' => $driver === 'sqlite'
                            ? database_path("tenants/{$dbName}.sqlite")
                            : $dbName,
                        'host' => config('database.connections.mysql.host', '127.0.0.1'),
                        'port' => config('database.connections.mysql.port', '3306'),
                        'username' => config('database.connections.mysql.username', 'root'),
                        'password' => config('database.connections.mysql.password', ''),
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ]
                ]);

                DB::purge('tenant');

                $previousDefault = config('database.default');
                config(['database.default' => 'tenant']);

                $ownerUser = User::where('email', $tenant->owner_login_email)->first();
                if ($ownerUser) {
                    $ownerUser->update([
                        'password' => $newPassword,
                    ]);
                }

                config(['database.default' => $previousDefault]);
                DB::purge($previousDefault);
            } catch (Exception $e) {
                if (isset($previousDefault)) {
                    config(['database.default' => $previousDefault]);
                }
                logger()->error('Updating user password in tenant DB failed: ' . $e->getMessage());
            }
        }

        LandlordAuditService::record(
            'tenant_regenerate_password',
            $tenant,
            "Mot de passe propriétaire régénéré pour la boutique : {$tenant->name}"
        );

        return back()->with('success', 'Le mot de passe propriétaire a été régénéré avec succès.');
    }

    /**
     * Register the current active database as a legacy tenant.
     */
    public function registerLegacy()
    {
        // Check if already registered
        $exists = Tenant::where('slug', 'boutique-actuelle')->exists();
        if ($exists) {
            return redirect()->route('landlord.tenants.index')->with('error', 'La boutique actuelle est déjà enregistrée.');
        }

        $defaultConnection = config('database.default');
        $currentDatabaseName = config("database.connections.{$defaultConnection}.database");

        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Boutique actuelle',
            'slug' => 'boutique-actuelle',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => $currentDatabaseName,
            'database_username' => config("database.connections.{$defaultConnection}.username"),
            'database_password' => config("database.connections.{$defaultConnection}.password"),
            'database_host' => config("database.connections.{$defaultConnection}.host"),
            'database_port' => config("database.connections.{$defaultConnection}.port"),
            'timezone' => 'Africa/Douala',
            'currency' => 'FCFA',
            'notes' => 'Boutique créée avant le passage multi-tenant',
        ]);

        // Audit log
        LandlordAuditService::record(
            'tenant_register_legacy',
            $tenant,
            "Boutique actuelle (legacy) enregistrée sous la base : {$currentDatabaseName}"
        );

        return redirect()->route('landlord.tenants.show', $tenant)->with('success', 'Boutique actuelle enregistrée avec succès.');
    }
}
