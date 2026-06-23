<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantBackup;
use App\Services\Platform\LandlordAuditService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TenantBackupService
{
    /**
     * Create a backup record in pending status.
     */
    public function createBackupRecord(Tenant $tenant, string $type = 'manual', array $metadata = []): TenantBackup
    {
        $filename = $this->generateFilename($tenant);
        $disk = config('platform.backups.disk', 'local');
        $pathPrefix = config('platform.backups.path', 'platform-backups');
        $relativePath = $pathPrefix . '/' . $tenant->slug . '/' . $filename;

        $backup = TenantBackup::create([
            'tenant_id' => $tenant->id,
            'filename' => $filename,
            'path' => $relativePath,
            'disk' => $disk,
            'size_bytes' => 0,
            'status' => 'pending',
            'backup_type' => $type,
            'started_at' => now(),
            'database_name' => $tenant->database_name ?: config('database.connections.mysql.database'),
            'metadata' => $metadata,
        ]);

        LandlordAuditService::record(
            'backup_created',
            $tenant,
            "Enregistrement de sauvegarde créé pour la boutique : {$tenant->name}. Type : {$type}",
            ['backup_id' => $backup->id, 'filename' => $filename]
        );

        return $backup;
    }

    /**
     * Coordonne la création et le lancement d'une sauvegarde manuelle.
     */
    public function runManualBackup(Tenant $tenant, ?int $createdBy = null): TenantBackup
    {
        $metadata = [];
        if ($createdBy) {
            $metadata['created_by_user_id'] = $createdBy;
        }

        if (!config('platform.backups.enabled', false)) {
            $metadata['mode'] = 'simulation';
        }

        $backup = $this->createBackupRecord($tenant, 'manual', $metadata);
        if ($createdBy) {
            $backup->update(['created_by' => $createdBy]);
        }

        return $this->runBackup($backup);
    }

    /**
     * Run the backup execution logic.
     */
    public function runBackup(TenantBackup $backup): TenantBackup
    {
        $tenant = $backup->tenant;
        if (!$tenant) {
            return $this->markFailed($backup, "La boutique associée à ce backup est introuvable.");
        }

        if (!$this->canBackupTenant($tenant)) {
            return $this->markFailed($backup, "La base de données de cette boutique n’est pas encore provisionnée.");
        }

        $this->markRunning($backup);

        // Simulation Mode
        if (!config('platform.backups.enabled', false)) {
            $metadata = $backup->metadata ?: [];
            $metadata['mode'] = 'simulation';
            $backup->update(['metadata' => $metadata]);

            // Simulation success
            sleep(1); // Simulate work
            return $this->markCompleted($backup, $backup->path, 1024 * 150); // 150 Ko simulated
        }

        // Real Mode
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_') . '.sql';
        try {
            $dbConfig = $this->resolveDatabaseConfig($tenant);
            
            $process = new Process([
                config('platform.backups.mysqldump_path', 'mysqldump'),
                '--host=' . $dbConfig['host'],
                '--port=' . $dbConfig['port'],
                '--user=' . $dbConfig['username'],
                $dbConfig['database'],
            ], null, [
                'MYSQL_PWD' => $dbConfig['password']
            ]);

            $process->setTimeout(300);
            $process->setOutputFile($tempFile);
            $process->run();

            if (!$process->isSuccessful()) {
                // Do not log password
                $errorOutput = $process->getErrorOutput();
                $cleanedError = str_replace($dbConfig['password'], '******', $errorOutput);
                throw new \Exception("mysqldump failed: " . $cleanedError);
            }

            // Check if file has content
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception("Le fichier de dump généré est vide.");
            }

            // Calculate checksum
            $checksum = md5_file($tempFile);
            $size = filesize($tempFile);

            // Store file to configured disk
            $disk = $backup->disk ?: config('platform.backups.disk', 'local');
            $stream = fopen($tempFile, 'r+');
            
            Storage::disk($disk)->put($backup->path, $stream);
            
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Update checksum
            $backup->update(['checksum' => $checksum]);

            return $this->markCompleted($backup, $backup->path, $size);

        } catch (\Throwable $e) {
            Log::error("Backup execution failed for tenant {$tenant->slug}: " . $e->getMessage());
            return $this->markFailed($backup, $e->getMessage());
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Mark backup as running.
     */
    public function markRunning(TenantBackup $backup): TenantBackup
    {
        $backup->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        LandlordAuditService::record(
            'backup_started',
            $backup->tenant,
            "Sauvegarde {$backup->filename} démarrée",
            ['backup_id' => $backup->id]
        );

        return $backup;
    }

    /**
     * Mark backup as completed.
     */
    public function markCompleted(TenantBackup $backup, string $path, ?int $sizeBytes = null): TenantBackup
    {
        $backup->update([
            'status' => 'completed',
            'finished_at' => now(),
            'size_bytes' => $sizeBytes,
            'path' => $path,
        ]);

        LandlordAuditService::record(
            'backup_completed',
            $backup->tenant,
            "Sauvegarde {$backup->filename} terminée avec succès. Taille : " . $backup->sizeForHumans(),
            ['backup_id' => $backup->id, 'size_bytes' => $sizeBytes]
        );

        return $backup;
    }

    /**
     * Mark backup as failed.
     */
    public function markFailed(TenantBackup $backup, string $error): TenantBackup
    {
        // Safe cleaning to prevent secrets leaking
        $cleanError = substr($error, 0, 500);

        $backup->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $cleanError,
        ]);

        LandlordAuditService::record(
            'backup_failed',
            $backup->tenant,
            "Sauvegarde {$backup->filename} échouée. Erreur : {$cleanError}",
            ['backup_id' => $backup->id, 'error' => $cleanError]
        );

        return $backup;
    }

    /**
     * Generate backup filename.
     */
    public function generateFilename(Tenant $tenant): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        return "{$timestamp}_{$tenant->slug}.sql";
    }

    /**
     * Resolve database credentials dynamically.
     */
    public function resolveDatabaseConfig(Tenant $tenant): array
    {
        if ($tenant->provisioning_status === 'legacy_current_db') {
            $defaultConn = config('database.default');
            return [
                'host' => config("database.connections.{$defaultConn}.host", '127.0.0.1'),
                'port' => config("database.connections.{$defaultConn}.port", '3306'),
                'database' => config("database.connections.{$defaultConn}.database"),
                'username' => config("database.connections.{$defaultConn}.username"),
                'password' => config("database.connections.{$defaultConn}.password"),
            ];
        }

        return [
            'host' => $tenant->database_host ?: config('database.connections.mysql.host', '127.0.0.1'),
            'port' => $tenant->database_port ?: config('database.connections.mysql.port', '3306'),
            'database' => $tenant->database_name,
            'username' => $tenant->database_username ?: config('database.connections.mysql.username', 'root'),
            'password' => $tenant->database_password ?: '',
        ];
    }

    /**
     * Check if a backup can be executed for the tenant.
     */
    public function canBackupTenant(Tenant $tenant): bool
    {
        // Prepared tenants don't have database provisioned yet
        return $tenant->provisioning_status !== 'prepared';
    }

    /**
     * Clean old backups keeping only N last.
     */
    public function cleanupOldBackups(Tenant $tenant): int
    {
        $keep = config('platform.backups.keep_last', 10);
        
        $backups = TenantBackup::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->orderBy('finished_at', 'desc')
            ->get();

        if ($backups->count() <= $keep) {
            return 0;
        }

        $deletedCount = 0;
        $backupsToDelete = $backups->slice($keep);

        foreach ($backupsToDelete as $backup) {
            // Delete file from disk
            if ($backup->path && Storage::disk($backup->disk)->exists($backup->path)) {
                Storage::disk($backup->disk)->delete($backup->path);
            }
            
            // Delete record
            $backup->delete();
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            LandlordAuditService::record(
                'backup_cleanup',
                $tenant,
                "Nettoyage de {$deletedCount} anciennes sauvegardes pour la boutique : {$tenant->name}",
                ['deleted_count' => $deletedCount]
            );
        }

        return $deletedCount;
    }
}
