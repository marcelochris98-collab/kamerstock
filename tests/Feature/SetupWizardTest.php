<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $userWithoutPermission;
    private $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);
        $userRole = Role::create([
            'name' => 'Vendeur',
            'slug' => 'vendeur',
        ]);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $this->userWithoutPermission = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role_id' => $userRole->id,
            'is_active' => true,
        ]);

        // Create settings
        $this->settings = Setting::create([
            'id' => 1,
            'shop_name' => 'Initial Shop',
            'currency' => 'USD',
            'tax_rate' => 10,
            'invoice_prefix' => 'INV',
            'business_type' => 'quincaillerie',
        ]);
    }

    public function test_guest_cannot_access_setup_wizard()
    {
        $response = $this->get(route('admin.setup.index'));
        $response->assertStatus(302);
    }

    public function test_user_without_permission_cannot_access_setup_wizard()
    {
        $this->actingAs($this->userWithoutPermission);
        $response = $this->get(route('admin.setup.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_setup_wizard()
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.setup.index'));
        $response->assertStatus(200);
        $response->assertSee('Initial Shop');
        $response->assertSee('Quincaillerie');
    }

    public function test_admin_can_save_setup_wizard_general_and_business_type()
    {
        $this->actingAs($this->admin);

        // Pre-create 'Boissons' to test duplicate verification
        Category::create(['name' => 'Boissons']);

        $response = $this->post(route('admin.setup.store'), [
            'shop_name' => 'Wizard Shop',
            'currency' => 'EUR',
            'tax_rate' => 20,
            'invoice_prefix' => 'WIZ',
            'business_type' => 'superette',
            'categories' => ['Boissons', 'Épicerie', 'Hygiène'],
            'units' => ['piece', 'kg'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertEquals('Wizard Shop', $this->settings->shop_name);
        $this->assertEquals('EUR', $this->settings->currency);
        $this->assertEquals('superette', $this->settings->business_type);
        $this->assertEquals(['piece', 'kg'], $this->settings->enabled_units);
        $this->assertEquals('configured', $this->settings->setup_step);

        // Verify categories (Boissons existed, Épicerie and Hygiène created, total should be 3)
        $this->assertEquals(3, Category::count());
        $this->assertTrue(Category::where('name', 'Épicerie')->exists());
        $this->assertTrue(Category::where('name', 'Hygiène')->exists());
    }

    public function test_admin_can_finish_setup_wizard()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.setup.finish'));
        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));

        $this->settings->refresh();
        $this->assertTrue($this->settings->setup_completed);
        $this->assertEquals('completed', $this->settings->setup_step);
        $this->assertNotNull($this->settings->setup_completed_at);
    }

    public function test_admin_can_reset_setup_wizard()
    {
        $this->actingAs($this->admin);

        // Mark as completed first
        $this->settings->update([
            'setup_completed' => true,
            'setup_completed_at' => now(),
            'setup_step' => 'completed',
        ]);

        $response = $this->post(route('admin.setup.reset'));
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.setup.index'));

        $this->settings->refresh();
        $this->assertFalse($this->settings->setup_completed);
        $this->assertNull($this->settings->setup_step);
        $this->assertNull($this->settings->setup_completed_at);
    }
}
