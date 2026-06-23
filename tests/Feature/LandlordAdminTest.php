<?php

namespace Tests\Feature;

use App\Models\Platform\LandlordUser;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\LandlordAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LandlordAdminTest extends TestCase
{
    use RefreshDatabase;

    private $landlordUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord tables specifically for testing
        Artisan::call('migrate', [
            '--database' => 'landlord',
            '--path' => 'database/migrations/2026_06_22_170000_create_platform_tables.php'
        ]);

        // Seed plans
        Artisan::call('db:seed', [
            '--class' => 'PlatformPlanSeeder'
        ]);

        // Seed landlord user
        $this->landlordUser = LandlordUser::create([
            'name' => 'Super Admin Test',
            'email' => 'admin@kamerstock.cm',
            'password' => bcrypt('Admin@2026'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_can_view_landlord_login_page()
    {
        $response = $this->get(route('landlord.login'));
        $response->assertStatus(200);
        $response->assertSee('KamerStock Landlord');
    }

    public function test_landlord_user_can_login_with_correct_credentials()
    {
        $response = $this->post(route('landlord.login.store'), [
            'email' => 'admin@kamerstock.cm',
            'password' => 'Admin@2026',
        ]);

        $response->assertRedirect(route('landlord.dashboard'));
        $this->assertAuthenticatedAs($this->landlordUser, 'landlord');
        
        // Verify audit log created
        $this->assertTrue(LandlordAuditLog::where('action', 'login')->exists());
    }

    public function test_landlord_user_cannot_login_if_inactive()
    {
        $this->landlordUser->update(['is_active' => false]);

        $response = $this->post(route('landlord.login.store'), [
            'email' => 'admin@kamerstock.cm',
            'password' => 'Admin@2026',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('landlord');
    }

    public function test_guest_cannot_access_landlord_dashboard()
    {
        $response = $this->get(route('landlord.dashboard'));
        $response->assertRedirect(route('landlord.login'));
    }

    public function test_authenticated_landlord_can_access_dashboard()
    {
        $response = $this->actingAs($this->landlordUser, 'landlord')->get(route('landlord.dashboard'));
        $response->assertStatus(200);
        $response->assertSee("Console Super Admin");
        $response->assertSee("Confidentialité des Boutiques");
    }

    public function test_landlord_can_create_a_tenant()
    {
        $plan = Plan::first();

        $response = $this->actingAs($this->landlordUser, 'landlord')->post(route('landlord.tenants.store'), [
            'name' => 'Boutique Alpha',
            'slug' => 'boutique-alpha',
            'owner_name' => 'Jean Paul',
            'owner_email' => 'jean@alpha.com',
            'owner_phone' => '237699999999',
            'business_type' => 'quincaillerie',
            'status' => 'trial',
            'plan_id' => $plan->id,
            'trial_ends_at' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('landlord.tenants.index'));
        $this->assertTrue(Tenant::where('slug', 'boutique-alpha')->exists());
        $this->assertTrue(LandlordAuditLog::where('action', 'tenant_create')->exists());
    }

    public function test_landlord_can_suspend_a_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Boutique Suspend',
            'slug' => 'boutique-suspend',
            'owner_email' => 'owner@suspend.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'landlord')->post(route('landlord.tenants.suspend', $tenant));
        
        $response->assertStatus(302);
        $tenant->refresh();
        $this->assertEquals('suspended', $tenant->status);
        $this->assertNotNull($tenant->suspended_at);
        $this->assertTrue(LandlordAuditLog::where('action', 'tenant_suspend')->exists());
    }

    public function test_landlord_can_activate_a_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Boutique Active',
            'slug' => 'boutique-active',
            'owner_email' => 'owner@active.com',
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        $response = $this->actingAs($this->landlordUser, 'landlord')->post(route('landlord.tenants.activate', $tenant));
        
        $response->assertStatus(302);
        $tenant->refresh();
        $this->assertEquals('active', $tenant->status);
        $this->assertNull($tenant->suspended_at);
        $this->assertTrue(LandlordAuditLog::where('action', 'tenant_activate')->exists());
    }

    public function test_landlord_can_put_tenant_in_readonly_mode()
    {
        $tenant = Tenant::create([
            'name' => 'Boutique RO',
            'slug' => 'boutique-ro',
            'owner_email' => 'owner@ro.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'landlord')->post(route('landlord.tenants.read_only', $tenant));
        
        $response->assertStatus(302);
        $tenant->refresh();
        $this->assertEquals('read_only', $tenant->status);
        $this->assertNotNull($tenant->read_only_at);
        $this->assertTrue(LandlordAuditLog::where('action', 'tenant_readonly')->exists());
    }
}
