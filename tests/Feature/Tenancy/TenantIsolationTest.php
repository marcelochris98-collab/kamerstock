<?php

namespace Tests\Feature\Tenancy;

use App\Models\Platform\Tenant;
use App\Models\Platform\LandlordUser;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantSecurityService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use DatabaseMigrations;

    private Tenant $legacyTenant;
    private Tenant $preparedTenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord database
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        app(TenantContext::class)->clear();

        // Create tenant records
        $this->legacyTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Actuelle',
            'slug' => 'boutique-actuelle',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => 'kamerstock',
        ]);

        $this->preparedTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Prepared',
            'slug' => 'boutique-prepared',
            'status' => 'trial',
            'provisioning_status' => 'prepared',
            'database_password' => encrypt('secret_db_pass'),
            'owner_password_plain' => encrypt('plain_owner_pass'),
        ]);
    }

    protected function tearDown(): void
    {
        Config::set('platform.tenancy_enabled', false);
        DB::setDefaultConnection(config('database.default'));
        DB::purge('tenant');
        parent::tearDown();
    }

    /**
     * Test that landlord login page loads successfully.
     */
    public function test_landlord_login_page_loads()
    {
        $response = $this->get(route('landlord.login'));
        $response->assertStatus(200);
    }

    /**
     * Test that tenant-debug route is forbidden outside local/testing environments.
     */
    public function test_tenant_debug_is_forbidden_outside_local()
    {
        $originalEnv = $this->app->environment();

        // Fake production environment
        $this->app->detectEnvironment(fn() => 'production');

        $response = $this->get('/tenant-debug?tenant=boutique-actuelle');
        $response->assertStatus(404);

        // Restore environment
        $this->app->detectEnvironment(fn() => $originalEnv);
    }

    /**
     * Test that dashboard works without a tenant resolved (passes through to normal auth check).
     */
    public function test_dashboard_works_without_tenant()
    {
        $response = $this->get('/dashboard');
        // Since no user is logged in, it redirects to boutique login page
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that dashboard works with a legacy tenant resolved.
     */
    public function test_dashboard_works_with_legacy_tenant_query()
    {
        Config::set('platform.tenancy_enabled', true);
        $response = $this->get('/dashboard?tenant=boutique-actuelle');
        // Redirects to normal user login route
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that a prepared tenant does not crash the tenant debug route (when local/testing is active).
     */
    public function test_prepared_tenant_does_not_crash_tenant_debug()
    {
        $response = $this->get('/tenant-debug?tenant=boutique-prepared');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'tenant_resolved' => true,
            'slug' => 'boutique-prepared',
            'provisioning_status' => 'prepared',
        ]);
    }

    /**
     * Test that sensitive fields are hidden from Tenant models and their serialization.
     */
    public function test_sensitive_fields_are_hidden_from_tenant_array()
    {
        $tenant = Tenant::on('landlord')->where('slug', 'boutique-prepared')->first();
        
        $this->assertNotNull($tenant);
        
        // Assert fields are hidden when calling toArray()
        $arrayData = $tenant->toArray();
        $this->assertArrayNotHasKey('database_password', $arrayData);
        $this->assertArrayNotHasKey('owner_password_plain', $arrayData);

        // Assert service also cleans it correctly
        $securityService = new TenantSecurityService();
        $sanitized = $securityService->sanitizeTenantForDisplay($tenant);
        $this->assertArrayNotHasKey('database_password', $sanitized);
        $this->assertArrayNotHasKey('owner_password_plain', $sanitized);
    }

    /**
     * Test that tenant resolver resolves tenant from session when no other strategy matches.
     */
    public function test_resolver_can_resolve_by_session()
    {
        Config::set('platform.tenancy_enabled', true);
        
        $resolver = app(\App\Services\Tenancy\TenantResolver::class);
        
        // Simuler une requete avec session contenant current_tenant_slug
        $request = \Illuminate\Http\Request::create('/dashboard', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('current_tenant_slug', 'boutique-actuelle');
        
        $tenant = $resolver->resolveFromRequest($request);
        
        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-actuelle', $tenant->slug);
    }
}
