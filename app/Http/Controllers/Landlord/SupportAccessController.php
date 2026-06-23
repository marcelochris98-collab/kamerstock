<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\LandlordUser;
use App\Services\Platform\SupportAccessService;
use App\Services\Platform\LandlordAuditService;
use Illuminate\Http\Request;

class SupportAccessController extends Controller
{
    protected SupportAccessService $service;

    public function __construct(SupportAccessService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of support accesses.
     */
    public function index(Request $request)
    {
        $query = SupportAccess::with(['tenant']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $accesses = $query->orderBy('created_at', 'desc')->paginate(15);
        $tenants = Tenant::on('landlord')->orderBy('name')->get();

        return view('landlord.support.index', compact('accesses', 'tenants'));
    }

    /**
     * Show the form for creating a new support access.
     */
    public function create()
    {
        $tenants = Tenant::on('landlord')->orderBy('name')->get();
        $landlordUsers = LandlordUser::on('landlord')->orderBy('name')->get();

        return view('landlord.support.create', compact('tenants', 'landlordUsers'));
    }

    /**
     * Store a newly created support access in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:platform_tenants,id',
            'reason' => 'required|string|max:500',
            'duration' => 'required|string|in:30_minutes,1_hour,24_hours,30m,1h,24h',
        ]);

        $tenant = Tenant::on('landlord')->findOrFail($request->tenant_id);

        $access = $this->service->createAccess($tenant, [
            'reason' => $request->reason,
            'duration' => $request->duration,
            'requested_by' => auth('landlord')->id(),
            'granted_to' => auth('landlord')->id(),
        ]);

        return redirect()->route('landlord.support.show', $access)
            ->with('success', "Demande d'accès support créée avec succès. Vous devez maintenant l'activer.");
    }

    /**
     * Show the form for support access on a specific tenant.
     */
    public function tenantSupport(Tenant $tenant)
    {
        return view('landlord.support.create', [
            'tenant' => $tenant,
            'tenants' => Tenant::on('landlord')->orderBy('name')->get(),
            'landlordUsers' => LandlordUser::on('landlord')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store support access from tenant show page.
     */
    public function tenantSupportStore(Request $request, Tenant $tenant)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'required|string|in:30_minutes,1_hour,24_hours,30m,1h,24h',
        ]);

        $access = $this->service->createAccess($tenant, [
            'reason' => $request->reason,
            'duration' => $request->duration,
            'requested_by' => auth('landlord')->id(),
            'granted_to' => auth('landlord')->id(),
        ]);

        // Auto-activate for ease of support (if created directly by landlord admin)
        $this->service->activateAccess($access);

        return redirect()->route('landlord.tenants.show', $tenant)
            ->with('success', "Accès support créé et activé avec succès.");
    }

    /**
     * Display the specified support access.
     */
    public function show(SupportAccess $supportAccess)
    {
        return view('landlord.support.show', [
            'access' => $supportAccess->load(['tenant']),
        ]);
    }

    /**
     * Activate the support access.
     */
    public function activate(SupportAccess $supportAccess)
    {
        $this->service->activateAccess($supportAccess);

        return redirect()->back()
            ->with('success', "Accès support activé avec succès.");
    }

    /**
     * Revoke the support access.
     */
    public function revoke(SupportAccess $supportAccess)
    {
        $this->service->revokeAccess($supportAccess);

        return redirect()->back()
            ->with('success', "Accès support révoqué avec succès.");
    }

    /**
     * Expire all past active support accesses.
     */
    public function expireOld()
    {
        $count = $this->service->expireOldAccesses();

        return redirect()->back()
            ->with('success', "{$count} accès support expiré(s) nettoyé(s) avec succès.");
    }

    /**
     * Enter support session mode for the tenant.
     */
    public function enter(SupportAccess $supportAccess)
    {
        if (!$supportAccess->canBeUsed()) {
            return redirect()->back()
                ->with('error', "Cet accès support ne peut pas être utilisé (inactif, expiré ou révoqué).");
        }

        // Store support session data
        session([
            'support_access_id' => $supportAccess->id,
            'support_tenant_id' => $supportAccess->tenant_id,
        ]);

        LandlordAuditService::record(
            'support_access_entered',
            $supportAccess->tenant,
            "L'administrateur Landlord a commencé une session de support sur la boutique : {$supportAccess->tenant->name}"
        );

        $tenant = $supportAccess->tenant;

        if ($tenant->provisioning_status === 'legacy_current_db') {
            return redirect()->to(route('dashboard', [
                'tenant' => $tenant->slug,
                'support_access' => $supportAccess->id,
            ]))->with('success', "Vous consultez cette boutique en mode support temporaire.");
        }

        if ($tenant->provisioning_status === 'prepared') {
            return redirect()->to(route('tenant.pending', [
                'tenant' => $tenant->slug,
            ]))->with('success', "Vous consultez cette boutique en mode support temporaire.");
        }

        // Default routing to tenant dashboard
        return redirect()->to(route('dashboard', [
            'tenant' => $tenant->slug,
            'support_access' => $supportAccess->id,
        ]))->with('success', "Vous consultez cette boutique en mode support temporaire.");
    }
}
