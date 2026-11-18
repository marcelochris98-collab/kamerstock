<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TenantProvisioner
{
    public function __construct(private TenantDatabaseManager $databaseManager) {}

    public function provision(Tenant $tenant, array $ownerData): User
    {
        $tenant->update(['status' => Tenant::STATUS_PROVISIONING]);

        try {
            $this->databaseManager->createDatabaseForTenant($tenant);
            $this->databaseManager->configureForTenant($tenant);

            $migrationExitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
                '--no-interaction' => true,
            ]);

            if ($migrationExitCode !== 0) {
                throw new RuntimeException('Les migrations tenant ont échoué : '.Artisan::output());
            }

            $seederExitCode = Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => RoleAndPermissionSeeder::class,
                '--force' => true,
                '--no-interaction' => true,
            ]);

            if ($seederExitCode !== 0) {
                throw new RuntimeException('La création des rôles tenant a échoué : '.Artisan::output());
            }

            $adminRole = Role::on('tenant')->where('slug', 'admin')->firstOrFail();

            $user = User::on('tenant')->create([
                'name' => $ownerData['name'],
                'email' => $ownerData['email'],
                'password' => Hash::make($ownerData['password']),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);

            Setting::on('tenant')->firstOrCreate(
                ['id' => 1],
                ['shop_name' => $tenant->name]
            );

            $tenant->update(['status' => Tenant::STATUS_ACTIVE]);

            return $user;
        } catch (Throwable $exception) {
            $tenant->update(['status' => Tenant::STATUS_FAILED]);

            Log::error('Échec du provisionnement tenant.', [
                'tenant_id' => $tenant->id,
                'exception' => $exception->getMessage(),
            ]);

            throw new TenantProvisioningException(
                "La création de l'espace entreprise a échoué. Réessayez plus tard ou contactez le support.",
                $exception
            );
        } finally {
            $this->databaseManager->restoreDefaultConnection();
        }
    }
}
