<?php

namespace Tests\Feature\Tenancy;

use App\Models\Platform\Tenant;
use App\Models\Platform\SupportAccess;
use App\Models\Platform\LandlordUser;
use App\Services\Platform\SupportAccessService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Tests\TestCase;

class SupportAccessTest extends TestCase
{
    use DatabaseMigrations;

    private Tenant $tenant;
    private LandlordUser $landlordUser;
    private SupportAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord database
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        app(TenantContext::class)->clear();
        $this->service = app(SupportAccessService::class);

        // Create landlord user and tenant
        $this->landlordUser = LandlordUser::on('landlord')->create([
            'name' => 'Support Admin',
            'email' => 'admin@landlord.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->tenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Test',
            'slug' => 'boutique-test',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => 'kamerstock',
        ]);
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection(config('database.default'));
        DB::purge('tenant');
        parent::tearDown();
    }

    /**
     * Test creating a support access request (mode pending).
     */
    public function test_can_create_support_access()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Intervention technique',
            'duration' => '1_hour',
        ]);

        $this->assertDatabaseHas('platform_support_accesses', [
            'id' => $access->id,
            'status' => 'pending',
            'reason' => 'Intervention technique',
        ], 'landlord');
    }

    /**
     * Test activating a support access request.
     */
    public function test_can_activate_support_access()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Intervention technique',
            'duration' => '1_hour',
        ]);

        $this->service->activateAccess($access);

        $access->refresh();

        $this->assertEquals('active', $access->status);
        $this->assertNotNull($access->starts_at);
        $this->assertNotNull($access->ends_at);
        $this->assertTrue($access->ends_at->isFuture());
    }

    /**
     * Test revoking a support access.
     */
    public function test_can_revoke_support_access()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Intervention',
            'duration' => '30m',
        ]);
        $this->service->activateAccess($access);
        $this->service->revokeAccess($access);

        $access->refresh();

        $this->assertEquals('revoked', $access->status);
        $this->assertNotNull($access->revoked_at);
    }

    /**
     * Test entering in support mode.
     */
    public function test_can_enter_support_mode_if_valid()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Support',
            'duration' => '1_hour',
        ]);
        $this->service->activateAccess($access);

        $response = $this->get(route('landlord.support.enter', $access));

        // Should redirect to boutique dashboard
        $response->assertRedirect('/dashboard?tenant=boutique-test&support_access=' . $access->id);

        // Verify session variables are set
        $response->assertSessionHas('support_access_id', $access->id);
        $response->assertSessionHas('support_tenant_id', $this->tenant->id);
    }

    /**
     * Test entering support mode is forbidden if access is revoked.
     */
    public function test_cannot_enter_support_mode_if_revoked()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Support',
            'duration' => '1_hour',
        ]);
        $this->service->activateAccess($access);
        $this->service->revokeAccess($access);

        $response = $this->get(route('landlord.support.enter', $access));
        $response->assertSessionMissing('support_access_id');
    }

    /**
     * Test exiting support mode.
     */
    public function test_can_exit_support_mode()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Support',
            'duration' => '1_hour',
        ]);
        $this->service->activateAccess($access);

        // Set session
        session([
            'support_access_id' => $access->id,
            'support_tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(route('support.exit'));

        // Should clear session
        $response->assertSessionMissing('support_access_id');
        $response->assertSessionMissing('support_tenant_id');
    }

    /**
     * Test artisan command to expire past accesses.
     */
    public function test_artisan_command_expires_old_accesses()
    {
        $this->actingAs($this->landlordUser, 'landlord');

        $access = $this->service->createAccess($this->tenant, [
            'reason' => 'Support',
            'duration' => '30m',
        ]);
        $this->service->activateAccess($access);

        // Force ends_at to past manually
        $access->update([
            'ends_at' => Carbon::now()->subMinutes(10),
        ]);

        // Run command
        Artisan::call('platform:expire-support-accesses');

        $access->refresh();
        $this->assertEquals('expired', $access->status);
    }
}
