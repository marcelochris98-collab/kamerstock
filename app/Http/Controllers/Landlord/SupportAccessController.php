<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\SupportAccess;

class SupportAccessController extends Controller
{
    /**
     * Display a listing of support accesses.
     */
    public function index()
    {
        $supportAccesses = SupportAccess::with('tenant')->latest()->paginate(15);
        return view('landlord.support.index', compact('supportAccesses'));
    }
}
