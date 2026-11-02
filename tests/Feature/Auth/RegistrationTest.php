<?php

namespace Tests\Feature\Auth;

use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'PlatformPlanSeeder',
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Lancez votre boutique en 2 minutes');
        $response->assertSee('Nom du commerce ou boutique');
        $response->assertSee('kamerstock.cm/t/');
    }

    public function test_check_slug_endpoint_reports_availability(): void
    {
        $response = $this->getJson('/check-slug?slug=ma-nouvelle-boutique');

        $response->assertStatus(200);
        $response->assertJson([
            'available' => true,
            'slug' => 'ma-nouvelle-boutique',
        ]);
    }

    public function test_check_slug_blocks_reserved_slugs(): void
    {
        $response = $this->getJson('/check-slug?slug=admin');

        $response->assertStatus(200);
        $response->assertJson([
            'available' => false,
            'slug' => 'admin',
        ]);
    }

    public function test_new_boutiques_can_self_register(): void
    {
        $response = $this->post('/register', [
            'shop_name'             => 'Quincaillerie Test Self',
            'slug'                  => 'quincaillerie-test-self',
            'business_type'         => 'quincaillerie',
            'name'                  => 'Gérant Test',
            'email'                 => 'gerant.test@example.com',
            'password'              => 'Password@2026',
            'password_confirmation' => 'Password@2026',
            'terms'                 => '1',
        ]);

        $response->assertSessionHasNoErrors();

        // Vérifier que le tenant a bien été créé dans la base centrale platform_tenants
        $tenant = Tenant::on('landlord')->where('slug', 'quincaillerie-test-self')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Quincaillerie Test Self', $tenant->name);
        $this->assertEquals('trial', $tenant->status);

        // La réponse doit soit rediriger vers le dashboard du tenant, soit vers pending
        $response->assertStatus(302);
    }

    public function test_new_boutiques_can_self_register_with_custom_business_type(): void
    {
        $response = $this->post('/register', [
            'shop_name'             => 'Atelier Menuiserie Moderne',
            'slug'                  => 'atelier-menuiserie-moderne',
            'business_type'         => 'Menuiserie & Ébénisterie',
            'name'                  => 'Artisan Gérant',
            'email'                 => 'artisan@example.com',
            'password'              => 'Password@2026',
            'password_confirmation' => 'Password@2026',
            'terms'                 => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $tenant = Tenant::on('landlord')->where('slug', 'atelier-menuiserie-moderne')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('autre', $tenant->business_type);
        $this->assertEquals('Menuiserie & Ébénisterie', $tenant->business_type_custom);
        $response->assertStatus(302);
    }
}

