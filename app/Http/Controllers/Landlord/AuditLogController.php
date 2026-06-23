<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\LandlordAuditLog;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index()
    {
        $auditLogs = LandlordAuditLog::with(['landlordUser', 'tenant'])->latest()->paginate(25);
        return view('landlord.audit_logs.index', compact('auditLogs'));
    }
}
