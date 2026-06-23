<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\TenantBackup;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index()
    {
        $backups = TenantBackup::with('tenant')->latest()->paginate(15);
        return view('landlord.backups.index', compact('backups'));
    }
}
