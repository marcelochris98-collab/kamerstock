<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantDatabaseManager;
use App\Services\Tenancy\TenantResolver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use DatabaseMigrations;

    private Tenant $legacyTenant;
    private Tenant $preparedTenant;
    private Tenant $readyTenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord tables
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        // Clear resolved context
        app(TenantContext::class)->clear();

        // Create a legacy tenant
        $this->legacyTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Legacy',
            'slug' => 'boutique-legacy',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => config('database.connections.mysql.database', 'kamerstock'),
        ]);

        // Create a prepared tenant
        $this->preparedTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Prepared',
            'slug' => 'boutique-prepared',
            'status' => 'trial',
            'provisioning_status' => 'prepared',
        ]);

        // Create a ready tenant
        $this->readyTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Ready',
            'slug' => 'boutique-ready',
            'status' => 'active',
            'provisioning_status' => 'migrated',
            'database_name' => ':memory:',
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'database_username' => 'root',
            'database_password' => encrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        // Restore config to default state
        Config::set('platform.tenancy_enabled', false);

        // Reset the default connection to avoid affecting subsequent tests
        DB::setDefaultConnection(config('database.default'));

        // Purge the tenant connection to release SQLite resources if used
        DB::purge('tenant');
        DB::disconnect('tenant');

        parent::tearDown();
    }

    public function test_resolver_can_resolve_by_query_parameter()
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('/dashboard', 'GET', ['tenant' => 'boutique-legacy']);

        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    public function test_resolver_can_resolve_by_path_prefix()
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('/t/boutique-legacy/dashboard', 'GET');

        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    public function test_resolver_can_resolve_by_subdomain()
    {
        Config::set('platform.central_domains', ['localhost', 'kamerstock.cm']);
        $resolver = app(TenantResolver::class);

        $request = Request::create('http://boutique-legacy.localhost/dashboard', 'GET');
        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    public function test_resolver_ignores_landlord_and_assets()
    {
        $resolver = app(TenantResolver::class);

        $request1 = Request::create('/landlord/dashboard', 'GET', ['tenant' => 'boutique-legacy']);
        $this->assertNull($resolver->resolveFromRequest($request1));

        $request2 = Request::create('/assets/logo.png', 'GET', ['tenant' => 'boutique-legacy']);
        $this->assertNull($resolver->resolveFromRequest($request2));
    }

    public function test_middleware_redirects_prepared_tenant_to_pending_page()
    {
        Config::set('platform.tenancy_enabled', true);

        $response = $this->get('/dashboard?tenant=boutique-prepared');

        // Should redirect to tenant.pending
        $response->assertRedirect(route('tenant.pending', ['tenant' => 'boutique-prepared']));
    }

    public function test_middleware_does_not_redirect_legacy_tenant()
    {
        // When tenancy_enabled is false, legacy tenant should be resolved and pass through
        Config::set('platform.tenancy_enabled', false);

        $response = $this->get('/dashboard?tenant=boutique-legacy');
        
        // Since no user is logged in, it should redirect to login route (standard CheckAuth logic)
        // but NOT redirect to pending page!
        $response->assertRedirect(route('login'));
        $this->assertEquals('boutique-legacy', app(TenantContext::class)->slug());
    }

    public function test_tenant_database_manager_keeps_default_database_for_legacy_or_prepared()
    {
        Config::set('platform.tenancy_enabled', true);
        $manager = app(TenantDatabaseManager::class);

        // For legacy tenant
        $manager->configureForTenant($this->legacyTenant);
        $this->assertEquals(config('database.default'), DB::getDefaultConnection());

        // For prepared tenant
        $manager->configureForTenant($this->preparedTenant);
        $this->assertEquals(config('database.default'), DB::getDefaultConnection());
    }

    public function test_tenant_database_manager_switches_connection_config_for_ready_tenant()
    {
        Config::set('platform.tenancy_enabled', true);
        Config::set('platform.tenant_resolution_enabled', true);
        Config::set('platform.tenant_database_switching.enabled', true);
        Config::set('platform.tenant_database_switching.allow_local', true);

        $manager = app(TenantDatabaseManager::class);

        try {
            $manager->configureForTenant($this->readyTenant);

            $tenantConfig = config('database.connections.tenant');
            if ($tenantConfig['driver'] === 'sqlite') {
                $this->assertEquals(':memory:', $tenantConfig['database']);
                $this->assertNull($tenantConfig['host']);
                $this->assertNull($tenantConfig['password']);
            } else {
                $this->assertEquals($this->readyTenant->database_name, $tenantConfig['database']);
                $this->assertEquals('127.0.0.1', $tenantConfig['host']);
                $this->assertEquals('password', $tenantConfig['password']);
            }
            $this->assertEquals('tenant', DB::getDefaultConnection());
        } finally {
            $manager->switchToDefault();
        }
    }

    public function test_tenant_debug_route_shows_context_correctly()
    {
        $response = $this->get(route('tenant.debug', ['tenant' => 'boutique-legacy']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'tenant_resolved' => true,
            'slug' => 'boutique-legacy',
            'provisioning_status' => 'legacy_current_db',
        ]);
        
        // Assert sensitive information is not exposed
        $response->assertJsonMissing(['database_password']);
        $response->assertJsonMissing(['owner_password_plain']);
    }
}
