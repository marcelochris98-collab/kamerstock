<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = 'backups';
        
        if (!Storage::exists($backupDir)) {
            Storage::makeDirectory($backupDir);
        }

        $files = Storage::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(Storage::size($file)),
                    'created_at' => \Carbon\Carbon::createFromTimestamp(Storage::lastModified($file))->format('d/m/Y H:i:s'),
                    'timestamp' => Storage::lastModified($file),
                ];
            }
        }

        // Trier par date decroissante
        usort($backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        return view('admin.backups', compact('backups'));
    }

    public function create()
    {
        try {
            $sql = "-- KamerStock Database Backup\n";
            $sql .= "-- Generated at: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $key = "Tables_in_" . $dbName;

            foreach ($tables as $tableObj) {
                $table = $tableObj->$key;
                
                // Structure de la table
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $sql .= "-- Table Structure: `{$table}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Données de la table
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Table Data: `{$table}`\n";
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $columns = array_keys($rowArray);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                        
                        $escapedValues = array_map(function($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes($val) . "'";
                        }, array_values($rowArray));

                        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
            Storage::put('backups/' . $filename, $sql);

            ActivityLog::record('backup.create', "Sauvegarde de base de données générée : {$filename}");

            return back()->with('success', "Sauvegarde {$filename} générée avec succès.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()]);
        }
    }

    public function download($filename)
    {
        $path = 'backups/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::download($path);
    }

    public function destroy($filename)
    {
        $path = 'backups/' . $filename;

        if (Storage::exists($path)) {
            Storage::delete($path);
            ActivityLog::record('backup.delete', "Sauvegarde de base de données supprimée : {$filename}");
            return back()->with('success', "Sauvegarde supprimée.");
        }

        return back()->withErrors(['error' => 'Sauvegarde introuvable.']);
    }

    public function restore($filename)
    {
        $path = 'backups/' . $filename;

        if (!Storage::exists($path)) {
            return back()->withErrors(['error' => 'Sauvegarde introuvable.']);
        }

        try {
            $sqlContent = Storage::get($path);
            
            DB::transaction(function () use ($sqlContent) {
                // Desactiver les cles et executer le dump SQL
                DB::unprepared($sqlContent);
            });

            ActivityLog::record('backup.restore', "Base de données restaurée depuis la sauvegarde : {$filename}");

            app(\App\Services\NotificationService::class)->notifyByPermission(
                'settings.manage',
                'backup_restored',
                'Base de données restaurée',
                "La base de données a été restaurée avec succès depuis le fichier {$filename}.",
                route('admin.backups.index'),
                ['filename' => $filename],
                'admin'
            );

            return back()->with('success', "Base de données restaurée avec succès depuis {$filename} !");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Erreur de restauration : ' . $e->getMessage()]);
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
