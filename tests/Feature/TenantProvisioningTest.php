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

    public function test_migrate_tenant_command_refuses_legacy_current_db()
    {
        $tenant = Tenant::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Legacy Boutique',
            'slug' => 'legacy-boutique',
            'owner_name' => 'Legacy Owner',
            'owner_email' => 'legacy@tenant.test',
            'status' => 'active',
            'database_name' => 'legacy_db',
            'provisioning_status' => 'legacy_current_db',
        ]);

        $exitCode = Artisan::call('platform:migrate-tenant', [
            'slug' => $tenant->slug,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('La boutique actuelle legacy ne doit pas être migrée', $output);
    }

    public function test_migrate_tenant_command_refuses_prepared_tenant()
    {
        $tenant = Tenant::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Prepared Boutique',
            'slug' => 'prepared-boutique',
            'owner_name' => 'Prepared Owner',
            'owner_email' => 'prepared@tenant.test',
            'status' => 'trial',
            'database_name' => 'prepared_tenant_db',
            'provisioning_status' => 'prepared',
        ]);

        $exitCode = Artisan::call('platform:migrate-tenant', [
            'slug' => $tenant->slug,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('La base n\'est pas encore créée', $output);
    }

    public function test_migrate_tenant_command_migrates_database_created_tenant_with_sqlite()
    {
        config(['platform.enable_database_provisioning' => true]);

        $plan = Plan::first();
        $tenantData = [
            'name' => 'Tenant SQLite Command',
            'owner_email' => 'owner@sqlitetenant.test',
            'owner_name' => 'Tenant Owner',
            'status' => 'trial',
            'database_name' => 'kamerstock_tenant_sqlite_command',
            'database_username' => 'root',
            'database_password' => '',
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'provisioning_status' => 'database_created',
            'owner_login_email' => 'owner@sqlitetenant.test',
            'owner_password_plain' => 'Secret1234',
        ];

        $tenantData['slug'] = 'tenant-sqlite-command';
        $tenant = Tenant::create($tenantData);

        $dbPath = database_path("tenants/{$tenant->database_name}.sqlite");
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0755, true);
        }
        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }

        $exitCode = Artisan::call('platform:migrate-tenant', [
            'slug' => $tenant->slug,
            '--force' => true,
        ]);

        $tenant->refresh();
        $this->assertEquals(0, $exitCode);
        $this->assertEquals('migrated', $tenant->provisioning_status);
        $this->assertTrue(file_exists($dbPath));

        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }
    }

    public function test_owner_user_is_created_in_tenant_database()
    {
        config(['platform.enable_database_provisioning' => true]);

        $tenant = Tenant::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Tenant Owner DB',
            'slug' => 'tenant-owner-db',
            'owner_name' => 'Tenant Owner',
            'owner_email' => 'owner@ownertenant.test',
            'owner_login_email' => 'owner@ownertenant.test',
            'owner_password_plain' => 'Secret1234',
            'database_name' => 'kamerstock_tenant_owner_db',
            'database_username' => 'root',
            'database_password' => '',
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'status' => 'trial',
            'provisioning_status' => 'database_created',
        ]);

        $dbPath = database_path("tenants/{$tenant->database_name}.sqlite");
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0755, true);
        }
        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }

        $service = new TenantProvisioningService();
        $service->migrateTenant($tenant);
        $tenant->refresh();

        $this->assertEquals('migrated', $tenant->provisioning_status);
        $this->assertTrue(file_exists($dbPath));

        config(['database.default' => 'tenant']);
        config(['database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => $dbPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        DB::purge('tenant');
        $user = DB::connection('tenant')->table('users')->where('email', $tenant->owner_login_email)->first();
        $this->assertNotNull($user);

        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }
    }
}
