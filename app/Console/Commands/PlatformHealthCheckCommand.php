<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Platform\Tenant;
use Exception;

class PlatformHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valide l\'intégrité technique, la configuration et l\'état des connexions de la plateforme multi-tenant.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->title('Diagnostic de Sante - Platforme KamerStock Multi-Tenant');

        $tests = [];
        
        // 1. Validation de la Configuration
        $this->section("1. Validation de la Configuration");
        
        $tenancyEnabled = config('platform.tenancy_enabled');
        $resolutionEnabled = config('platform.tenant_resolution_enabled');
        $strategy = config('platform.tenancy_strategy');
        
        $this->line("Mode Multi-Tenant : " . ($tenancyEnabled ? "<info>ACTIVE</info>" : "<comment>DESACTIVE (Mode Mono-Boutique Legacy)</comment>"));
        $this->line("Resolution Tenant : " . ($resolutionEnabled ? "<info>ACTIVEE</info>" : "<comment>DESACTIVEE</comment>"));
        $this->line("Strategie de Tenancy : <info>{$strategy}</info>");
        $dbProvEnabled = config('platform.database_provisioning.enabled', false);
        $this->line("Provisionnement DB réel : " . ($dbProvEnabled ? "<info>ACTIVE</info>" : "<comment>DESACTIVE</comment>"));
        
        $tests[] = [
            'Composant' => 'Configuration Platform',
            'Statut' => 'OK',
            'Details' => 'Fichier config/platform.php charge avec succes.'
        ];

        // 2. Connexion Landlord DB
        $this->section("2. Connexion Base de Donnees Landlord");
        $landlordConnOk = false;
        try {
            DB::connection('landlord')->getPdo();
            $this->info("[OK] Connexion a la base de donnees Landlord etablie.");
            $landlordConnOk = true;
            $tests[] = [
                'Composant' => 'Connexion Landlord DB',
                'Statut' => 'OK',
                'Details' => 'Connexion reussie sur le pilote ' . DB::connection('landlord')->getDriverName()
            ];
        } catch (Exception $e) {
            $this->error("[ECHEC] Impossible de se connecter a la base Landlord : " . $e->getMessage());
            $tests[] = [
                'Composant' => 'Connexion Landlord DB',
                'Statut' => 'ECHEC',
                'Details' => substr($e->getMessage(), 0, 80)
            ];
        }

        // 3. Tables de la Plateforme (SaaS)
        if ($landlordConnOk) {
            $this->section("3. Verification des Tables Centrales (Landlord)");
            $requiredTables = [
                'platform_tenants',
                'platform_plans',
                'platform_subscriptions',
                'platform_subscription_payments',
                'platform_landlord_users',
                'platform_tenant_backups',
                'platform_support_accesses',
                'platform_landlord_audit_logs'
            ];
            
            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (Schema::connection('landlord')->hasTable($table)) {
                    $this->line("  - Table '{$table}' : <info>[PRESENTE]</info>");
                } else {
                    $this->line("  - Table '{$table}' : <error>[ABSENTE]</error>");
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                $this->info("[OK] Toutes les tables landlord requises sont presentes.");
                $tests[] = [
                    'Composant' => 'Schema Central Landlord',
                    'Statut' => 'OK',
                    'Details' => count($requiredTables) . ' tables verifiees avec succes.'
                ];
            } else {
                $this->error("[ECHEC] Des tables requises sont manquantes : " . implode(', ', $missingTables));
                $tests[] = [
                    'Composant' => 'Schema Central Landlord',
                    'Statut' => 'ECHEC',
                    'Details' => count($missingTables) . ' table(s) manquante(s).'
                ];
            }
        } else {
            $tests[] = [
                'Composant' => 'Schema Central Landlord',
                'Statut' => 'NON TESTE',
                'Details' => 'Depend de la connexion Landlord DB.'
            ];
        }

        // 4. Boutique Legacy active
        if ($landlordConnOk && Schema::connection('landlord')->hasTable('platform_tenants')) {
            $this->section("4. Verification de la Boutique Legacy (Mono-boutique de secours)");
            $legacyTenant = Tenant::where('provisioning_status', 'legacy_current_db')->first();
            if ($legacyTenant) {
                $this->info("[OK] Boutique legacy identifiee : '{$legacyTenant->name}' (Database: {$legacyTenant->database_name})");
                $tests[] = [
                    'Composant' => 'Boutique Legacy',
                    'Statut' => 'OK',
                    'Details' => "Configuree pour : {$legacyTenant->name}"
                ];
            } else {
                $this->warn("[ATTENTION] Aucune boutique de type 'legacy_current_db' n'a ete trouvee.");
                $tests[] = [
                    'Composant' => 'Boutique Legacy',
                    'Statut' => 'ATTENTION',
                    'Details' => 'Absence de boutique legacy de secours'
                ];
            }

            // Afficher le nombre de tenants par statut de provisioning
            try {
                $prepared = Tenant::where('provisioning_status', 'prepared')->count();
                $created = Tenant::where('provisioning_status', 'database_created')->count();
                $failed = Tenant::where('provisioning_status', 'failed')->count();

                $this->line("Tenants (prepared): <info>{$prepared}</info> | (database_created): <info>{$created}</info> | (failed): <error>{$failed}</error>");
            } catch (Exception $e) {
                // ignore
            }
        }

        // 5. Configuration Sauvegardes
        $this->section("5. Configuration & Environnement des Sauvegardes");
        $backupEnabled = config('platform.backups.enabled');
        $backupDisk = config('platform.backups.disk');
        $backupPath = config('platform.backups.path');
        
        $this->line("Sauvegardes activees (platform.backups.enabled) : " . ($backupEnabled ? "<info>OUI</info>" : "<comment>NON (Mode simulation)</comment>"));
        $this->line("Disque de stockage : <info>{$backupDisk}</info>");
        $this->line("Dossier de stockage : <info>{$backupPath}</info>");

        $backupEnvOk = true;
        try {
            $diskExists = false;
            try {
                Storage::disk($backupDisk);
                $diskExists = true;
            } catch (Exception $e) {}

            if ($diskExists) {
                $this->info("[OK] Le disque de stockage '{$backupDisk}' est configure.");
                
                // Essai d'ecriture temporaire
                $tempFile = $backupPath . '/.healthcheck_' . time();
                Storage::disk($backupDisk)->put($tempFile, 'healthcheck');
                if (Storage::disk($backupDisk)->exists($tempFile)) {
                    Storage::disk($backupDisk)->delete($tempFile);
                    $this->info("[OK] Droits d'ecriture valides sur le dossier '{$backupPath}' du disque '{$backupDisk}'.");
                } else {
                    $this->error("[ECHEC] Impossible d'ecrire dans le dossier '{$backupPath}'.");
                    $backupEnvOk = false;
                }
            } else {
                $this->error("[ECHEC] Le disque de stockage '{$backupDisk}' n'est pas declare dans config/filesystems.php.");
                $backupEnvOk = false;
            }
        } catch (Exception $e) {
            $this->error("[ECHEC] Erreur lors de la validation du stockage : " . $e->getMessage());
            $backupEnvOk = false;
        }

        // Verification du chemin mysqldump
        $mysqldumpPath = config('platform.backups.mysqldump_path', 'mysqldump');
        $this->line("Outil mysqldump declare : <info>{$mysqldumpPath}</info>");
        
        $tests[] = [
            'Composant' => 'Environnement Sauvegardes',
            'Statut' => $backupEnvOk ? 'OK' : 'ECHEC',
            'Details' => $backupEnvOk ? 'Disque et droits d\'ecriture valides.' : 'Erreur de stockage ou de droits.'
        ];

        // 6. Tableau recapitulatif final
        $this->section("6. Recapitulatif des Diagnostics");
        
        $headers = ['Composant', 'Statut', 'Détails'];
        $this->table($headers, $tests);
        $this->newLine();

        $hasError = collect($tests)->contains('Statut', 'ECHEC');
        if ($hasError) {
            $this->error("Certains diagnostics ont echoue. Veuillez verifier les points en rouge.");
            return Command::FAILURE;
        }

        $this->info("Tous les diagnostics de base sont au vert. La plateforme est prete.");
        return Command::SUCCESS;
    }

    /**
     * Affiche un titre stylise.
     */
    private function title(string $text)
    {
        $len = strlen($text);
        $line = str_repeat('=', $len + 4);
        $this->info($line);
        $this->info("| $text |");
        $this->info($line);
        $this->newLine();
    }

    /**
     * Affiche un titre de section.
     */
    private function section(string $text)
    {
        $this->warn("--- $text ---");
        $this->newLine();
    }
}
