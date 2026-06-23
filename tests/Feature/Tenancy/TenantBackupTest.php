<?php

namespace Tests\Feature\Tenancy;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantBackup;
use App\Models\Platform\LandlordUser;
use App\Services\Platform\TenantBackupService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tests\TestCase;

class TenantBackupTest extends TestCase
{
    use DatabaseMigrations;

    private Tenant $tenant;
    private LandlordUser $landlordUser;
    private TenantBackupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrate landlord database
        Artisan::call('migrate', [
            '--database' => 'landlord',
        ]);

        $this->service = app(TenantBackupService::class);

        // Create landlord user and tenants
        $this->landlordUser = LandlordUser::on('landlord')->create([
            'name' => 'Landlord Admin',
            'email' => 'admin@landlord.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->tenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Actuelle',
            'slug' => 'boutique-actuelle',
            'status' => 'active',
            'provisioning_status' => 'legacy_current_db',
            'database_name' => 'kamerstock',
        ]);
    }

    /**
     * Test creating a backup record (pending).
     */
    public function test_can_create_backup_record()
    {
        $backup = $this->service->createBackupRecord($this->tenant, 'manual', ['test' => 'yes']);

        $this->assertDatabaseHas('platform_tenant_backups', [
            'id' => $backup->id,
            'status' => 'pending',
            'backup_type' => 'manual',
            'database_name' => 'kamerstock',
        ], 'landlord');
    }

    /**
     * Test simulation backup mode.
     */
    public function test_simulation_backup_completes_successfully()
    {
        Config::set('platform.backups.enabled', false);

        $backup = $this->service->runManualBackup($this->tenant, $this->landlordUser->id);

        $this->assertEquals('completed', $backup->status);
        $this->assertEquals('simulation', $backup->metadata['mode']);
        $this->assertGreaterThan(0, $backup->size_bytes);
    }

    /**
     * Test prepared tenants cannot be backed up.
     */
    public function test_prepared_tenant_backup_fails()
    {
        $preparedTenant = Tenant::on('landlord')->create([
            'name' => 'Boutique Préparée',
            'slug' => 'boutique-preparee',
            'status' => 'active',
            'provisioning_status' => 'prepared',
        ]);

        $backup = $this->service->runManualBackup($preparedTenant);

        $this->assertEquals('failed', $backup->status);
        $this->assertStringContainsString('pas encore provisionnée', $backup->error_message);
    }

    /**
     * Test backup cleanup command keeps only N last backups.
     */
    public function test_cleanup_keeps_last_n_backups()
    {
        Storage::fake('local');
        Config::set('platform.backups.keep_last', 3);
        Config::set('platform.backups.enabled', false); // Keep simulation to run fast

        // Create 5 completed backups
        for ($i = 1; $i <= 5; $i++) {
            $backup = $this->service->createBackupRecord($this->tenant, 'automatic');
            $this->service->markCompleted($backup, $backup->path, 1024);
            // shift finished_at to ensure ordering
            $backup->update(['finished_at' => Carbon::now()->subMinutes(10 - $i)]);
        }

        $this->assertEquals(5, TenantBackup::where('tenant_id', $this->tenant->id)->count());

        $cleaned = $this->service->cleanupOldBackups($this->tenant);

        $this->assertEquals(2, $cleaned);
        $this->assertEquals(3, TenantBackup::where('tenant_id', $this->tenant->id)->count());
    }

    /**
     * Test artisan platform:backup-tenant command.
     */
    public function test_artisan_backup_command()
    {
        Config::set('platform.backups.enabled', false);

        $this->artisan('platform:backup-tenant boutique-actuelle')
            ->assertExitCode(0)
            ->expectsOutputToContain('[OK] Sauvegarde créée avec succès !');
    }
}
