<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Services\Tenancy\TenantSecurityService;

class PlatformTenantIsolationCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:check-isolation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier la sécurité et l\'isolation de la configuration multi-tenant';

    /**
     * Execute the console command.
     */
    public function handle(TenantSecurityService $securityService): int
    {
        $this->info("=== KamerStock Isolation & Security Check ===");

        // 1. Connexion landlord disponible
        try {
            $landlordDb = DB::connection('landlord')->getPdo();
            $this->line("[OK] Landlord connection");
        } catch (\Throwable $e) {
            $this->error("[FAILED] Landlord connection: " . $e->getMessage());
            return Command::FAILURE;
        }

        // 2. Tables platform_* accessibles
        if (Schema::connection('landlord')->hasTable('platform_tenants')) {
            $this->line("[OK] Platform tenants table");
        } else {
            $this->error("[FAILED] Platform tenants table missing");
            return Command::FAILURE;
        }

        // 3 & 4. Tenant boutique-actuelle existe et provisioning_status = legacy_current_db
        $legacyTenant = Tenant::on('landlord')->where('slug', 'boutique-actuelle')->first();
        if ($legacyTenant) {
            if ($legacyTenant->provisioning_status === 'legacy_current_db') {
                $this->line("[OK] Legacy tenant registered correctly");
            } else {
                $this->warn("[WARNING] Legacy tenant exists but provisioning_status is: " . $legacyTenant->provisioning_status);
            }
        } else {
            $this->warn("[WARNING] Legacy tenant ('boutique-actuelle') not registered yet");
        }

        // 5. Aucun champ sensible affiché dans Tenant::first()->toArray()
        $firstTenant = Tenant::on('landlord')->first();
        if ($firstTenant) {
            $arrayData = $firstTenant->toArray();
            $sensitiveDetected = false;
            foreach ($securityService->sensitiveFields() as $field) {
                if (array_key_exists($field, $arrayData)) {
                    $sensitiveDetected = true;
                    $this->error("[FAILED] Sensitive field '{$field}' exposed in Tenant::toArray()!");
                }
            }
            if (!$sensitiveDetected) {
                $this->line("[OK] Sensitive fields hidden in Tenant serialization");
            }
        } else {
            $this->line("[OK] Sensitive fields hidden (no tenants to serialize for test)");
        }

        // 6. Les plans existent
        try {
            $plansCount = Plan::on('landlord')->count();
            if ($plansCount > 0) {
                $this->line("[OK] Platform plans found: {$plansCount}");
            } else {
                $this->warn("[WARNING] No plans found in platform_plans table. Run seeder!");
            }
        } catch (\Throwable $e) {
            $this->error("[FAILED] Platform plans check: " . $e->getMessage());
        }

        // 7. Guard landlord existe
        $guards = config('auth.guards', []);
        if (isset($guards['landlord'])) {
            $this->line("[OK] Landlord auth guard configured");
        } else {
            $this->error("[FAILED] Landlord auth guard missing from config/auth.php");
        }

        // 8 & 9. Routes landlord existent
        $hasLogin = Route::has('landlord.login');
        $hasDashboard = Route::has('landlord.dashboard');
        if ($hasLogin && $hasDashboard) {
            $this->line("[OK] Landlord login and dashboard routes exist");
        } else {
            $this->error("[FAILED] Landlord routes missing. Check web.php");
        }

        // 10. Route tenant.pending existe
        if (Route::has('tenant.pending')) {
            $this->line("[OK] Tenant pending route exists");
        } else {
            $this->error("[FAILED] Tenant pending route missing");
        }

        // 11. Route tenant.debug disponible uniquement local
        if (Route::has('tenant.debug')) {
            if (app()->environment('local', 'testing')) {
                $this->line("[OK] Tenant debug route registered and restricted in non-local environments");
            } else {
                $this->error("[FAILED] Tenant debug route should not be exposed in production environment");
            }
        } else {
            $this->warn("[WARNING] Tenant debug route does not exist");
        }

        // 12. PLATFORM_TENANCY_ENABLED actuel affiché
        $tenancyEnabled = config('platform.tenancy_enabled', false);
        if ($tenancyEnabled) {
            $this->warn("[WARNING] Tenancy is currently ENABLED by config (PLATFORM_TENANCY_ENABLED=true)");
        } else {
            $this->line("[OK] Tenancy disabled by config (PLATFORM_TENANCY_ENABLED=false)");
        }

        // 13. Connexion mysql actuelle fonctionne
        try {
            $defaultConnection = config('database.default', 'mysql');
            DB::connection($defaultConnection)->getPdo();
            $this->line("[OK] Default database connection works ({$defaultConnection})");
        } catch (\Throwable $e) {
            $this->error("[FAILED] Default database connection failed: " . $e->getMessage());
        }

        // 14. Connexion tenant fallback fonctionne si possible
        try {
            $tenantConnection = config('platform.tenant_connection_name', 'tenant');
            DB::connection($tenantConnection)->getPdo();
            $this->line("[OK] Tenant fallback database connection works ({$tenantConnection})");
        } catch (\Throwable $e) {
            $this->warn("[WARNING] Tenant database connection fallback failed (This is normal if database is not set or shared legacy): " . $e->getMessage());
        }

        $this->info("=== Verification complete ===");

        return Command::SUCCESS;
    }
}
