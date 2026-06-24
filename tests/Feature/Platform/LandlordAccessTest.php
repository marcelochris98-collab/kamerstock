<?php

namespace Tests\Feature\Platform;

use App\Models\Platform\LandlordUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LandlordAccessTest extends TestCase
{
    use RefreshDatabase;

    private $landlordUser;
    private $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Migration de la base Landlord pour le test
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        // Créer un utilisateur Landlord
        $this->landlordUser = LandlordUser::create([
            'name' => 'Super Admin Landlord',
            'email' => 'landlord@test.com',
            'password' => bcrypt('Password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Créer un utilisateur boutique standard (guard web par défaut)
        $this->tenantUser = User::factory()->create([
            'email' => 'shopowner@test.com',
            'password' => bcrypt('Password123'),
        ]);
    }

    /**
     * Un visiteur anonyme ne doit pas accéder à l'espace Landlord.
     */
    public function test_anonymous_user_cannot_access_landlord_dashboard()
    {
        $response = $this->get(route('landlord.dashboard'));
        $response->assertRedirect(route('landlord.login'));
    }

    /**
     * Un super admin Landlord connecté doit avoir accès au dashboard.
     */
    public function test_landlord_user_can_access_landlord_dashboard()
    {
        $response = $this->actingAs($this->landlordUser, 'landlord')
            ->get(route('landlord.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Console Super Admin');
    }

    /**
     * Un utilisateur standard de la boutique (guard web) ne doit pas accéder à l'espace Landlord.
     */
    public function test_tenant_user_cannot_access_landlord_dashboard()
    {
        $response = $this->actingAs($this->tenantUser, 'web')
            ->get(route('landlord.dashboard'));

        $response->assertRedirect(route('landlord.login'));
    }

    /**
     * Validation de la sécurité de la page de statistiques.
     */
    public function test_landlord_statistics_page_requires_landlord_auth()
    {
        // Visiteur anonyme
        $response = $this->get(route('landlord.statistics.index'));
        $response->assertRedirect(route('landlord.login'));

        // Super Admin Landlord
        $response = $this->actingAs($this->landlordUser, 'landlord')
            ->get(route('landlord.statistics.index'));
        $response->assertStatus(200);
    }
}
