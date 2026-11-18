<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class TenantDatabaseManager
{
    public function createDatabaseForTenant(Tenant $tenant): void
    {
        $centralConnection = config('database.central_connection');
        $driver = config("database.connections.{$centralConnection}.driver");

        if ($driver === 'sqlite') {
            $path = $this->databasePath($tenant);
            File::ensureDirectoryExists(dirname($path));

            if (! File::exists($path) && ! touch($path)) {
                throw new RuntimeException("Impossible de créer la base de données du tenant {$tenant->id}.");
            }

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::connection($centralConnection)->statement(
                'CREATE DATABASE IF NOT EXISTS `'.$this->databaseName($tenant).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );

            return;
        }

        throw new RuntimeException("Le pilote de base de données [{$driver}] n'est pas pris en charge pour les tenants.");
    }

    public function configureForTenant(Tenant $tenant): void
    {
        $centralConnection = config('database.central_connection');
        $connection = config("database.connections.{$centralConnection}");
        $connection['url'] = null;
        $connection['database'] = $this->tenantDatabase($tenant);

        config(['database.connections.tenant' => $connection]);
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
    }

    public function restoreDefaultConnection(): void
    {
        DB::purge('tenant');
        DB::setDefaultConnection(config('database.central_connection'));
    }

    private function tenantDatabase(Tenant $tenant): string
    {
        $driver = config('database.connections.'.config('database.central_connection').'.driver');

        return $driver === 'sqlite'
            ? $this->databasePath($tenant)
            : $this->databaseName($tenant);
    }

    private function databasePath(Tenant $tenant): string
    {
        return rtrim(config('platform.tenant_database_directory'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'tenant_'.$tenant->id.'.sqlite';
    }

    private function databaseName(Tenant $tenant): string
    {
        return 'kamerstock_tenant_'.$tenant->id;
    }
}
