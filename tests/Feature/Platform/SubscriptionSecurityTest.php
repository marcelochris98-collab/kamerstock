<?php

namespace Tests\Feature\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\SupportAccess;
use App\Services\Platform\SupportContext;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubscriptionSecurityTest extends TestCase
{
    use DatabaseMigrations;

    private Tenant $suspendedTenant;
    private Tenant $readOnlyTenant;
    private Tenant $activeTenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Migration Landlord pour les tests
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        app(TenantContext::class)->clear();
        app(SupportContext::class)->clear();

        // Configurer la tenancy à true pour activer les blocages
        Config::set('platform.tenancy_enabled', true);
        Config::set('platform.tenant_resolution_enabled', true);
        Config::set('platform.enforce_subscription_middleware', true);

        // Boutique Active
        $this->activeTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Active',
            'slug' => 'active-shop',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
        ]);

        // Boutique Suspendue
        $this->suspendedTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Suspendue',
            'slug' => 'suspended-shop',
            'status' => 'suspended',
            'suspended_at' => now(),
            'provisioning_status' => 'legacy_current_db',
        ]);

        // Boutique en Lecture Seule
        $this->readOnlyTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique RO',
            'slug' => 'ro-shop',
            'status' => 'read_only',
            'read_only_at' => now(),
            'provisioning_status' => 'legacy_current_db',
        ]);

        // Créer un rôle par défaut pour éviter les échecs de validation
        \App\Models\Role::create([
            'name' => 'Administrateur',
            'slug' => 'admin',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Config::set('platform.tenancy_enabled', false);
        DB::setDefaultConnection(config('database.default'));
        DB::purge('tenant');

        parent::tearDown();
    }

    /**
     * Teste qu'un accès à une boutique suspendue redirige vers la page pending.
     */
    public function test_suspended_tenant_is_redirected_to_pending_page()
    {
        $response = $this->get('/dashboard?tenant=suspended-shop');
        $response->assertRedirect(route('tenant.pending', ['tenant' => 'suspended-shop']));
    }

    /**
     * Teste que les requêtes GET sont autorisées sur une boutique en lecture seule.
     */
    public function test_read_only_tenant_allows_get_requests()
    {
        // On simule un utilisateur connecté pour passer la sécurité CheckAuth
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/dashboard?tenant=ro-shop');

        // Ne doit pas être bloqué par le middleware lecture seule (donc statut 200)
        $response->assertStatus(200);
    }

    /**
     * Teste que les requêtes d'écriture sont bloquées sur une boutique en lecture seule.
     */
    public function test_read_only_tenant_blocks_post_requests()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/admin/users?tenant=ro-shop', [
                'name' => 'Test User',
                'email' => 'test@user.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => 1,
            ]);

        // Doit être redirigé vers la page précédente (back) avec une erreur flash de lecture seule
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /**
     * Teste que les requêtes d'écriture en JSON retournent un code 403 sur une boutique en lecture seule.
     */
    public function test_read_only_tenant_blocks_json_post_requests_with_403()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/admin/users?tenant=ro-shop', [
                'name' => 'Test User',
                'email' => 'test@user.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => 1,
            ]);

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
    }

    /**
     * Teste que le mode support permet d'outrepasser les restrictions de lecture seule.
     */
    public function test_support_mode_bypasses_read_only_restriction()
    {
        $user = \App\Models\User::factory()->create();

        // Mock d'un accès support actif
        $supportAccess = SupportAccess::on('landlord')->create([
            'tenant_id' => $this->readOnlyTenant->id,
            'reason' => 'Intervention technique',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(25),
            'status' => 'active',
        ]);

        app(SupportContext::class)->setAccess($supportAccess);

        $response = $this->actingAs($user)
            ->post('/admin/users?tenant=ro-shop', [
                'name' => 'Test User Support',
                'email' => 'support@user.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => 1,
            ]);

        // Ne doit pas être bloqué avec une erreur de lecture seule (donc redirection standard suite à la tentative d'écriture, sans erreur flash de lecture seule)
        $response->assertSessionDoesntHaveErrors();
        $this->assertNotEquals("Boutique en lecture seule. Les modifications de données sont impossibles.", session('error'));
    }
}
