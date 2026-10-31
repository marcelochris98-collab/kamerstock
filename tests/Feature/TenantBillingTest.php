<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class TenantBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', ['--database' => 'landlord']);
        Artisan::call('db:seed', ['--class' => 'PlatformPlanSeeder']);

        // Créer un utilisateur et un paramètre boutique local
        $this->user = User::factory()->create([
            'email' => 'gerant@boutique-test.cm',
        ]);

        Setting::create([
            'shop_name' => 'Boutique Test Pro',
            'setup_completed' => false,
        ]);

        // Créer le tenant central
        $this->plan = Plan::on('landlord')->first();

        $this->tenant = Tenant::on('landlord')->create([
            'uuid' => 'test-uuid-billing',
            'name' => 'Boutique Test Pro',
            'slug' => 'boutique-test-pro',
            'owner_name' => 'Gerant Test',
            'owner_email' => 'gerant@boutique-test.cm',
            'business_type' => 'quincaillerie',
            'status' => 'trial',
            'provisioning_status' => 'legacy_current_db',
            'trial_ends_at' => now()->addDays(14),
            'plan_id' => $this->plan?->id,
            'database_name' => ':memory:',
        ]);

        if ($this->plan) {
            Subscription::on('landlord')->create([
                'tenant_id' => $this->tenant->id,
                'plan_id' => $this->plan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'price' => $this->plan->price_monthly,
            ]);
        }

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_dashboard_displays_quickstart_onboarding(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_slug' => $this->tenant->slug])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Guide de démarrage rapide');
        $response->assertSee('Création du compte');
        $response->assertSee('Ajouter vos stocks');
        $response->assertSee('Tester la caisse POS');
    }

    public function test_billing_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_slug' => $this->tenant->slug])
            ->get('/billing');

        $response->assertStatus(200);
        $response->assertSee('Abonnement & Facturation');
        $response->assertSee('Boutique Test Pro');
        $response->assertSee('Choisir ou renouveler votre plan');
    }

    public function test_self_service_checkout_activates_subscription(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_slug' => $this->tenant->slug])
            ->post('/billing/checkout', [
                'plan_id' => $this->plan->id,
                'billing_cycle' => 'monthly',
                'payment_method' => 'momo',
                'phone_number' => '670000000',
            ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('success');

        // Vérifier que le statut du tenant est passé à 'active'
        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->status);

        // Vérifier qu'un paiement a été créé
        $this->assertDatabaseHas('platform_subscription_payments', [
            'tenant_id' => $this->tenant->id,
            'payment_method' => 'momo',
            'status' => 'paid',
        ], 'landlord');
    }
}
