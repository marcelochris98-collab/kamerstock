<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantBackup;
use App\Services\Platform\TenantBackupService;
use App\Services\Platform\LandlordAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index(Request $request)
    {
        $query = TenantBackup::with('tenant')->latest();

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        $backups = $query->paginate(15)->withQueryString();
        $tenants = Tenant::on('landlord')->orderBy('name')->get();

        return view('landlord.backups.index', compact('backups', 'tenants'));
    }

    /**
     * Display the detailed backup information.
     */
    public function show(TenantBackup $backup)
    {
        // Nettoyage des metadonnées pour masquer d'éventuels secrets
        $metadata = $backup->metadata ?: [];
        unset($metadata['database_password']);
        unset($metadata['password']);
        
        return view('landlord.backups.show', compact('backup', 'metadata'));
    }

    /**
     * Store and trigger a new backup for the tenant.
     */
    public function store(Request $request, Tenant $tenant)
    {
        if (!config('platform.backups.allow_manual_backup', true)) {
            return back()->with('error', "La création manuelle de sauvegardes est désactivée.");
        }

        try {
            $backupService = app(TenantBackupService::class);
            $backup = $backupService->runManualBackup($tenant, auth('landlord')->id());

            if ($backup->isCompleted()) {
                return back()->with('success', "La sauvegarde de la boutique '{$tenant->name}' a été effectuée avec succès.");
            } else {
                return back()->with('error', "La sauvegarde a échoué. Erreur: " . $backup->error_message);
            }
        } catch (\Throwable $e) {
            return back()->with('error', "Erreur lors de la sauvegarde : " . $e->getMessage());
        }
    }

    /**
     * Re-run or trigger execution of a backup.
     */
    public function run(TenantBackup $backup)
    {
        try {
            $backupService = app(TenantBackupService::class);
            $backup = $backupService->runBackup($backup);

            if ($backup->isCompleted()) {
                return back()->with('success', "La sauvegarde a été exécutée avec succès.");
            } else {
                return back()->with('error', "L'exécution de la sauvegarde a échoué. Erreur: " . $backup->error_message);
            }
        } catch (\Throwable $e) {
            return back()->with('error', "Erreur d'exécution : " . $e->getMessage());
        }
    }

    /**
     * Download the backup file if allowed.
     */
    public function download(TenantBackup $backup)
    {
        if (!config('platform.backups.allow_download', false)) {
            abort(403, "Le téléchargement des sauvegardes est désactivé sur la plateforme.");
        }

        $disk = $backup->disk ?: config('platform.backups.disk', 'local');
        $path = $backup->path;

        if (!$path || !Storage::disk($disk)->exists($path)) {
            abort(404, "Le fichier de sauvegarde est introuvable sur le disque.");
        }

        // Vérification anti-traversée de chemin (directory containment)
        $pathPrefix = config('platform.backups.path', 'platform-backups');
        if (!str_starts_with($path, $pathPrefix)) {
            abort(400, "Chemin de sauvegarde invalide.");
        }

        // Journaliser le téléchargement
        LandlordAuditService::record(
            'backup_downloaded',
            $backup->tenant,
            "Fichier de sauvegarde {$backup->filename} téléchargé",
            ['backup_id' => $backup->id]
        );

        return Storage::disk($disk)->download($path, $backup->filename);
    }

    /**
     * Remove the backup record and delete the physical file.
     */
    public function destroy(TenantBackup $backup)
    {
        $disk = $backup->disk ?: config('platform.backups.disk', 'local');
        $path = $backup->path;

        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        LandlordAuditService::record(
            'backup_deleted',
            $backup->tenant,
            "Fichier de sauvegarde {$backup->filename} supprimé",
            ['backup_id' => $backup->id]
        );

        $backup->delete();

        return redirect()->route('landlord.backups.index')
            ->with('success', "Sauvegarde supprimée avec succès.");
    }
}
