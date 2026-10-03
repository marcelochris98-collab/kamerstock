<?php

namespace Tests\Feature;

use App\Models\Platform\LandlordUser;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Services\Platform\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewBoutiqueCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.database_provisioning.enabled' => true,
            'platform.tenant_migrations.enabled' => true,
        ]);

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);
    }

    public function test_landlord_can_create_new_boutique_with_custom_password_and_login(): void
    {
        // 1. Nettoyer la boutique de test si existante
        Tenant::on('landlord')->where('slug', 'boutique-test-auto')->forceDelete();

        $service = app(TenantProvisioningService::class);

        // 2. Créer la boutique avec un mot de passe personnalisé
        $tenantData = [
            'name' => 'Boutique Test Auto',
            'slug' => 'boutique-test-auto',
            'owner_name' => 'Propriétaire Test',
            'owner_email' => 'proprio.auto@kamerstock.cm',
            'owner_password' => 'MonSuperPass2026!',
            'status' => 'active',
        ];

        $tenant = $service->createTenant($tenantData);
        $service->provision($tenant);

        // Vérifier que la boutique est créée et migrée
        $this->assertEquals('migrated', $tenant->provisioning_status);
        $this->assertEquals('proprio.auto@kamerstock.cm', $tenant->owner_login_email);
        $this->assertEquals('MonSuperPass2026!', $tenant->owner_password_plain);

        // 3. Tester la connexion HTTP DIRECTEMENT sans passer le slug tenant (auto-détection par email)
        $responseAuto = $this->post('/login', [
            'email' => 'proprio.auto@kamerstock.cm',
            'password' => 'MonSuperPass2026!',
        ]);

        $responseAuto->assertRedirect('/dashboard?tenant=boutique-test-auto');
        $this->assertAuthenticated();

        // Se déconnecter
        $this->post('/logout');

        // 4. Tester la connexion HTTP AVEC le slug tenant explicite dans la requête
        $responseExplicit = $this->post('/login', [
            'tenant' => 'boutique-test-auto',
            'email' => 'proprio.auto@kamerstock.cm',
            'password' => 'MonSuperPass2026!',
        ]);

        $responseExplicit->assertRedirect('/dashboard?tenant=boutique-test-auto');
        $this->assertAuthenticated();
    }
}
