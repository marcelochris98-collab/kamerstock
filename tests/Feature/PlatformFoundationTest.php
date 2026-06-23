<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformFoundationTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord tables specifically for testing
        Artisan::call('migrate', [
            '--database' => 'landlord',
            '--path' => 'database/migrations/2026_06_22_170000_create_platform_tables.php'
        ]);

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
        Setting::create([
            'id' => 1,
            'shop_name' => 'Test Shop',
            'currency' => 'USD',
            'tax_rate' => 10,
            'invoice_prefix' => 'INV',
            'business_type' => 'quincaillerie',
        ]);
    }

    public function test_landlord_database_connection_can_be_accessed()
    {
        $this->assertTrue(Schema::connection('landlord')->hasTable('platform_plans'));
        $this->assertTrue(Schema::connection('landlord')->hasTable('platform_tenants'));
    }

    public function test_platform_plan_seeder_works()
    {
        Artisan::call('db:seed', [
            '--class' => 'PlatformPlanSeeder'
        ]);

        $this->assertEquals(4, Plan::count());
        $this->assertTrue(Plan::where('slug', 'pro')->exists());
        $this->assertTrue(Plan::where('slug', 'enterprise')->exists());
    }

    public function test_guest_cannot_access_platform_overview()
    {
        $response = $this->get(route('admin.platform.overview'));
        $response->assertStatus(302);
    }

    public function test_user_without_permission_cannot_access_platform_overview()
    {
        $this->actingAs($this->userWithoutPermission);
        $response = $this->get(route('admin.platform.overview'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_platform_overview()
    {
        $this->actingAs($this->admin);

        // Seed some data first
        Artisan::call('db:seed', [
            '--class' => 'PlatformPlanSeeder'
        ]);

        // Create a dummy tenant to verify display
        Tenant::create([
            'name' => 'Client Tenant',
            'slug' => 'client-tenant',
            'owner_email' => 'owner@tenant.com',
            'status' => 'active',
        ]);

        $response = $this->get(route('admin.platform.overview'));
        $response->assertRedirect(route('landlord.dashboard'));
    }
}
