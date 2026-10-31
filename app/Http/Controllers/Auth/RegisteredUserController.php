<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\LandlordAuditService;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected TenantProvisioningService $provisioningService;
    protected TenantDatabaseManager $dbManager;

    public function __construct(
        TenantProvisioningService $provisioningService,
        TenantDatabaseManager $dbManager
    ) {
        $this->provisioningService = $provisioningService;
        $this->dbManager = $dbManager;
    }

    /**
     * Display the self-service onboarding registration view.
     */
    public function create(): View
    {
        $businessTypes = config('business_types', []);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $defaultTrialDays = config('platform.default_trial_days', 14);

        return view('auth.register', compact('businessTypes', 'plans', 'defaultTrialDays'));
    }

    /**
     * Check if a store slug or subdomain is available via AJAX.
     */
    public function checkSlug(Request $request): JsonResponse
    {
        $raw = (string) $request->query('slug', '');
        $slug = Str::slug($raw);

        if (empty($slug)) {
            return response()->json([
                'available' => false,
                'slug' => '',
                'message' => 'Veuillez saisir un nom ou un identifiant valide.'
            ]);
        }

        // Mots-clés système réservés
        $reserved = [
            'admin', 'landlord', 'api', 't', 'dashboard', 'login', 'register',
            'settings', 'support', 'tenant', 'backups', 'test', 'platform',
            'superadmin', 'root', 'auth', 'welcome', 'app', 'system'
        ];

        if (in_array($slug, $reserved, true)) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'message' => "L'identifiant '{$slug}' est réservé par la plateforme."
            ]);
        }

        $exists = Tenant::on('landlord')->where('slug', $slug)->exists();

        if ($exists) {
            $suggestion = $this->provisioningService->generateSlug($slug);
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'suggestion' => $suggestion,
                'message' => "Cet identifiant est déjà pris. Suggestion : {$suggestion}"
            ]);
        }

        return response()->json([
            'available' => true,
            'slug' => $slug,
            'message' => "Parfait ! Cet identifiant de boutique est disponible."
        ]);
    }

    /**
     * Handle an incoming self-service registration request.
     * Creates the tenant, provisions the database, seeds the owner account, and auto-logs in.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $businessTypes = array_keys(config('business_types', []));
        $validTypesRule = !empty($businessTypes) ? 'in:' . implode(',', $businessTypes) : 'string';

        $validated = $request->validate([
            'shop_name'            => ['required', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\-_]+$/'],
            'business_type'        => ['required', 'string', $validTypesRule],
            'business_type_custom' => ['nullable', 'string', 'max:255'],
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password'             => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id'              => ['nullable', 'exists:landlord.platform_plans,id'],
            'terms'                => ['accepted'],
        ], [
            'shop_name.required'     => 'Le nom de votre commerce ou boutique est obligatoire.',
            'business_type.required' => 'Veuillez sélectionner votre secteur d’activité.',
            'name.required'          => 'Votre nom complet est obligatoire.',
            'email.required'         => 'Une adresse e-mail valide est obligatoire.',
            'password.required'      => 'Le mot de passe est obligatoire.',
            'password.confirmed'     => 'La confirmation du mot de passe ne correspond pas.',
            'terms.accepted'         => 'Veuillez accepter les conditions d’utilisation de la plateforme.',
        ]);

        // Déterminer ou dériver un slug propre et garanti unique
        $requestedSlug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['shop_name']);

        $finalSlug = $this->provisioningService->generateSlug($requestedSlug);

        // Déterminer le plan d'abonnement (le plan choisi ou le plan actif par défaut)
        $plan = null;
        if (!empty($validated['plan_id'])) {
            $plan = Plan::find($validated['plan_id']);
        }
        if (!$plan) {
            $plan = Plan::where('is_active', true)->orderBy('sort_order')->first();
        }

        $trialDays = config('platform.default_trial_days', 14);

        // Données d'initialisation du tenant
        $tenantData = [
            'name'                 => $validated['shop_name'],
            'slug'                 => $finalSlug,
            'owner_name'           => $validated['name'],
            'owner_email'          => $validated['email'],
            'owner_password'       => $validated['password'],
            'business_type'        => $validated['business_type'],
            'business_type_custom' => $validated['business_type_custom'] ?? null,
            'status'               => 'trial',
            'trial_days'           => $trialDays,
            'trial_ends_at'        => now()->addDays($trialDays),
            'plan_id'              => $plan?->id,
            'timezone'             => 'Africa/Douala',
            'currency'             => config('platform.default_currency', 'FCFA'),
        ];

        // 1. Créer le Tenant central dans platform_tenants
        $tenant = $this->provisioningService->createTenant($tenantData);

        // 2. Créer l'abonnement initial (période d'essai 14 jours)
        if ($plan) {
            $this->provisioningService->createInitialSubscription($tenant, $plan, $tenantData);
        }

        // 3. Déclencher le provisionnement de base de données (Mode B / SQLite / MySQL)
        $this->provisioningService->provision($tenant);
        $tenant->refresh();

        // 4. Enregistrer dans les logs d'audit Landlord
        try {
            LandlordAuditService::record(
                'tenant_self_registered',
                $tenant,
                "Nouvelle boutique inscrite en libre-service : {$tenant->name} ({$tenant->owner_login_email}) - Plan: " . ($plan?->name ?? 'Standard')
            );
        } catch (\Throwable $e) {
            // Ignorer si log indisponible
        }

        // 5. Connecter automatiquement le gérant dans sa nouvelle boutique
        if ($tenant->provisioning_status === 'migrated') {
            try {
                $this->dbManager->switchToTenant($tenant);

                $owner = User::where('email', $validated['email'])->first();

                if ($owner) {
                    event(new Registered($owner));
                    Auth::login($owner);

                    // Mémoriser le slug du tenant dans la session pour la résolution
                    $request->session()->put('current_tenant_slug', $tenant->slug);
                    $request->session()->regenerate();

                    return redirect()->route('dashboard', ['tenant' => $tenant->slug])
                        ->with('success', "Bienvenue sur KamerStock ! Votre boutique {$tenant->name} a été créée avec succès. Vous disposez de {$trialDays} jours d'essai gratuit.");
                }
            } catch (\Throwable $e) {
                logger()->error('Auto-login after self-service registration failed: ' . $e->getMessage(), [
                    'tenant' => $tenant->slug
                ]);
            }
        }

        // 6. Redirection douce vers la page d'attente si la base est en cours de création ou préparée
        return redirect()->route('tenant.pending', ['tenant' => $tenant->slug])
            ->with('success', "Votre boutique {$tenant->name} est créée ! Finalisation en cours...");
    }
}
