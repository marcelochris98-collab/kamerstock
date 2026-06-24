<?php

namespace Tests\Feature\Platform;

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

        // Migration de la base Landlord pour les tests
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        // Nettoyer le contexte
        app(TenantContext::class)->clear();

        // Création de tenants de test
        $this->legacyTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Legacy',
            'slug' => 'boutique-legacy',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => config('database.connections.mysql.database', 'kamerstock'),
        ]);

        $this->preparedTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Prepared',
            'slug' => 'boutique-prepared',
            'status' => 'trial',
            'provisioning_status' => 'prepared',
        ]);

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
        Config::set('platform.tenancy_enabled', false);
        DB::setDefaultConnection(config('database.default'));
        DB::purge('tenant');

        parent::tearDown();
    }

    /**
     * Teste la résolution d'un tenant via paramètre dans la requête.
     */
    public function test_resolver_can_resolve_by_query_parameter()
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('/dashboard', 'GET', ['tenant' => 'boutique-legacy']);

        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    /**
     * Teste la résolution d'un tenant via préfixe d'URL.
     */
    public function test_resolver_can_resolve_by_path_prefix()
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('/t/boutique-legacy/dashboard', 'GET');

        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    /**
     * Teste la résolution d'un tenant via sous-domaine.
     */
    public function test_resolver_can_resolve_by_subdomain()
    {
        Config::set('platform.central_domains', ['localhost', 'kamerstock.cm']);
        $resolver = app(TenantResolver::class);

        $request = Request::create('http://boutique-legacy.localhost/dashboard', 'GET');
        $tenant = $resolver->resolveFromRequest($request);

        $this->assertNotNull($tenant);
        $this->assertEquals('boutique-legacy', $tenant->slug);
    }

    /**
     * Teste que le résolveur de tenant ignore les URLs d'assets et landlord.
     */
    public function test_resolver_ignores_landlord_and_assets()
    {
        $resolver = app(TenantResolver::class);

        $request1 = Request::create('/landlord/dashboard', 'GET', ['tenant' => 'boutique-legacy']);
        $this->assertNull($resolver->resolveFromRequest($request1));

        $request2 = Request::create('/assets/logo.png', 'GET', ['tenant' => 'boutique-legacy']);
        $this->assertNull($resolver->resolveFromRequest($request2));
    }

    /**
     * Teste que les tenants préparés sont redirigés vers la page d'attente.
     */
    public function test_middleware_redirects_prepared_tenant_to_pending_page()
    {
        Config::set('platform.tenancy_enabled', true);

        $response = $this->get('/dashboard?tenant=boutique-prepared');

        $response->assertRedirect(route('tenant.pending', ['tenant' => 'boutique-prepared']));
    }

    /**
     * Teste que le gestionnaire de base de données tenant n'override pas pour legacy ou prepared.
     */
    public function test_tenant_database_manager_keeps_default_database_for_legacy_or_prepared()
    {
        Config::set('platform.tenancy_enabled', true);
        $manager = app(TenantDatabaseManager::class);

        $manager->configureForTenant($this->legacyTenant);
        $this->assertEquals(config('database.default'), DB::getDefaultConnection());

        $manager->configureForTenant($this->preparedTenant);
        $this->assertEquals(config('database.default'), DB::getDefaultConnection());
    }

    /**
     * Teste que les informations sensibles de la base de données ne sont pas divulguées sur la route de debug.
     */
    public function test_tenant_debug_route_does_not_expose_sensitive_credentials()
    {
        $response = $this->get(route('tenant.debug', ['tenant' => 'boutique-legacy']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'tenant_resolved' => true,
            'slug' => 'boutique-legacy',
        ]);
        
        $response->assertJsonMissing(['database_password']);
        $response->assertJsonMissing(['owner_password_plain']);
    }
}
