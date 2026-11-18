<?php

namespace Tests\Feature\Auth;

use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantDatabaseDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantDatabaseDirectory = storage_path('framework/testing/tenant-registration-'.bin2hex(random_bytes(6)));

        Config::set('database.connections.landlord.database', ':memory:');
        Config::set('platform.tenant_database_directory', $this->tenantDatabaseDirectory);
        DB::purge('landlord');

        Schema::connection('landlord')->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('landlord');
        DB::purge('tenant');
        File::deleteDirectory($this->tenantDatabaseDirectory);

        parent::tearDown();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'Boutique Test',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $tenant = Tenant::on('landlord')->where('slug', 'boutique-test')->firstOrFail();
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->status);

        $tenantUsers = DB::connection('tenant')->table('users');
        $this->assertSame('test@example.com', $tenantUsers->value('email'));
        $this->assertSame(
            'admin',
            DB::connection('tenant')->table('roles')->where('id', $tenantUsers->value('role_id'))->value('slug')
        );

        $this->get('/dashboard')->assertOk()->assertSee('boutique-test');
    }

    public function test_company_name_is_required_before_provisioning(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('company_name');
        $this->assertSame(0, Tenant::on('landlord')->count());
    }

    public function test_non_active_tenant_cannot_open_its_dashboard(): void
    {
        $this->post('/register', [
            'company_name' => 'Boutique Suspendue',
            'name' => 'Tenant Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $tenant = Tenant::on('landlord')->where('slug', 'boutique-suspendue')->firstOrFail();
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

        $this->get('/dashboard')
            ->assertForbidden()
            ->assertSee('Cet espace entreprise a été suspendu.');
    }

    public function test_each_company_gets_an_isolated_database_and_can_reuse_an_email(): void
    {
        $this->post('/register', [
            'company_name' => 'Boutique Alpha',
            'name' => 'Owner Alpha',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $firstTenant = Tenant::on('landlord')->where('slug', 'boutique-alpha')->firstOrFail();
        $firstDatabase = config('database.connections.tenant.database');

        $this->post('/logout')->assertRedirect(route('login', ['tenant' => $firstTenant->slug]));
        $this->get(route('login', ['tenant' => $firstTenant->slug]))->assertOk();
        $this->post('/login', [
            'tenant' => $firstTenant->slug,
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect(route('login', ['tenant' => $firstTenant->slug]));

        $this->post('/register', [
            'company_name' => 'Boutique Beta',
            'name' => 'Owner Beta',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $secondTenant = Tenant::on('landlord')->where('slug', 'boutique-beta')->firstOrFail();
        $secondDatabase = config('database.connections.tenant.database');

        $this->assertNotSame($firstDatabase, $secondDatabase);

        Config::set('database.connections.tenant.database', $firstDatabase);
        DB::purge('tenant');
        $this->assertSame(
            'Owner Alpha',
            DB::connection('tenant')->table('users')->where('email', 'owner@example.com')->value('name')
        );

        Config::set('database.connections.tenant.database', $secondDatabase);
        DB::purge('tenant');
        $this->assertSame(
            'Owner Beta',
            DB::connection('tenant')->table('users')->where('email', 'owner@example.com')->value('name')
        );

        $this->get('/dashboard?tenant='.$firstTenant->slug)->assertNotFound();

        $this->post('/logout')->assertRedirect(route('login', ['tenant' => $secondTenant->slug]));
        $legacyUser = User::factory()->create();
        $this->actingAs($legacyUser)->get('/dashboard?tenant='.$firstTenant->slug)->assertNotFound();
    }
}
