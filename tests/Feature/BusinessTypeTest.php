<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTypeTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role and user
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        // Create initial settings
        $this->settings = Setting::create([
            'id' => 1,
            'shop_name' => 'Initial Shop',
            'currency' => 'USD',
            'tax_rate' => 10,
            'invoice_prefix' => 'INV',
            'business_type' => 'quincaillerie',
        ]);
    }

    public function test_admin_can_update_business_type()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.settings.update'), [
            'shop_name' => 'My Superette',
            'currency' => 'XAF',
            'tax_rate' => 19.25,
            'invoice_prefix' => 'FAC',
            'business_type' => 'superette',
        ]);

        $response->assertStatus(302);
        
        $this->settings->refresh();
        $this->assertEquals('superette', $this->settings->business_type);
        $this->assertEquals('My Superette', $this->settings->shop_name);

        $service = app(\App\Services\BusinessTypeService::class);
        $this->assertEquals('Superette', $service->label());
        $this->assertEquals('Produits alimentaires et consommation courante', $service->subtitle());
        $this->assertEquals('Produit', $service->productLabel());
    }

    public function test_admin_can_set_custom_business_type()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.settings.update'), [
            'shop_name' => 'My Boutique',
            'currency' => 'XAF',
            'tax_rate' => 19.25,
            'invoice_prefix' => 'FAC',
            'business_type' => 'autre',
            'business_type_custom' => 'Librairie',
        ]);

        $response->assertStatus(302);
        
        $this->settings->refresh();
        $this->assertEquals('autre', $this->settings->business_type);
        $this->assertEquals('Librairie', $this->settings->business_type_custom);

        $service = app(\App\Services\BusinessTypeService::class);
        $this->assertEquals('Librairie', $service->label());
        $this->assertEquals('Librairie', $service->subtitle());
    }

    public function test_admin_can_create_default_categories_for_business_type()
    {
        $this->actingAs($this->admin);

        // Pre-create Boissons to test duplication logic
        Category::create(['name' => 'Boissons']);

        // Set to superette
        $this->settings->update(['business_type' => 'superette']);

        $response = $this->post(route('admin.settings.default-categories'));
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Check categories created: Boissons, Épicerie, Produits frais, Hygiène, Entretien
        // Since Boissons already existed, total categories should be 5
        $this->assertEquals(5, Category::count());
        $this->assertTrue(Category::where('name', 'Épicerie')->exists());
        $this->assertTrue(Category::where('name', 'Boissons')->exists());
    }
}
