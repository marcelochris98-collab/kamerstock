<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Services\Platform\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantProvisioningTest extends TestCase
{
    use RefreshDatabase;

    private $provisioningService;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        Artisan::call('db:seed', [
            '--class' => 'PlatformPlanSeeder'
        ]);

        $this->provisioningService = new TenantProvisioningService();
    }

    public function test_mode_a_provisioning_preparation()
    {
        // Force database provisioning disabled (Mode A)
        config(['platform.enable_database_provisioning' => false]);

        $plan = Plan::first();
        $tenantData = [
            'name' => 'Boutique Mode A',
            'owner_email' => 'owner@modea.com',
            'owner_name' => 'John Doe',
            'status' => 'trial',
            'trial_days' => 14,
            'plan_id' => $plan->id,
        ];

        $tenant = $this->provisioningService->createTenant($tenantData);
        $this->provisioningService->createInitialSubscription($tenant, $plan, $tenantData);
        
        $this->provisioningService->provision($tenant);

        $tenant->refresh();
        $this->assertEquals('prepared', $tenant->provisioning_status);
        $this->assertNull($tenant->provisioning_error);
        $this->assertNotNull($tenant->owner_password_plain);
        $this->assertEquals($tenant->owner_email, $tenant->owner_login_email);
        $this->assertEquals('kamerstock_tenant_boutique_mode_a', $tenant->database_name);
    }

    public function test_mode_b_provisioning_with_sqlite()
    {
        // Force database provisioning enabled (Mode B)
        config(['platform.enable_database_provisioning' => true]);

        $plan = Plan::first();
        $tenantData = [
            'name' => 'Boutique Mode B',
            'owner_email' => 'owner@modeb.com',
            'owner_name' => 'Jane Doe',
            'status' => 'trial',
            'trial_days' => 14,
            'plan_id' => $plan->id,
        ];

        $tenant = $this->provisioningService->createTenant($tenantData);
        $this->provisioningService->createInitialSubscription($tenant, $plan, $tenantData);

        // Ensure target file doesn't exist initially
        $dbPath = database_path("tenants/{$tenant->database_name}.sqlite");
        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }

        $this->provisioningService->provision($tenant);

        $tenant->refresh();
        
        // Assertions
        $this->assertEquals('migrated', $tenant->provisioning_status);
        $this->assertNull($tenant->provisioning_error);
        $this->assertTrue(file_exists($dbPath));

        // Clean up sqlite file with error suppression to avoid locks issues in PHP process
        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }
    }

    public function test_register_legacy_tenant()
    {
        $landlordUser = \App\Models\Platform\LandlordUser::create([
            'name' => 'Super Admin Test',
            'email' => 'admin@kamerstock.cm',
            'password' => bcrypt('Admin@2026'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($landlordUser, 'landlord')->post(route('landlord.tenants.register_legacy'));
        
        $tenant = Tenant::where('slug', 'boutique-actuelle')->first();
        $this->assertNotNull($tenant);
        $response->assertRedirect(route('landlord.tenants.show', $tenant));
        $this->assertEquals('legacy_current_db', $tenant->provisioning_status);
        $this->assertEquals('active', $tenant->status);
        
        // Test duplicate prevention
        $dupResponse = $this->actingAs($landlordUser, 'landlord')->post(route('landlord.tenants.register_legacy'));
        $dupResponse->assertRedirect(route('landlord.tenants.index'));
        $dupResponse->assertSessionHas('error');
    }
}
