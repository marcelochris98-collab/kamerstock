<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Platform\Plan;
use App\Models\Platform\LandlordAuditLog;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    /**
     * Display a listing of the plans.
     */
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('landlord.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('landlord.plans.create');
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landlord.platform_plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'max_users' => 'nullable|integer|min:1',
            'max_products' => 'nullable|integer|min:1',
            'max_clients' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
            'max_branches' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        // Process features text into array
        if ($request->filled('features_text')) {
            $features = array_filter(array_map('trim', explode("\n", $request->features_text)));
            $validated['features'] = array_values($features);
        } else {
            $validated['features'] = [];
        }

        $plan = Plan::create($validated);

        LandlordAuditLog::log('plan_create', "Plan d'abonnement créé : {$plan->name}", null, ['plan_id' => $plan->id]);

        return redirect()->route('landlord.plans.index')->with('success', "Le plan d'abonnement a été créé.");
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        $featuresText = is_array($plan->features) ? implode("\n", $plan->features) : '';
        return view('landlord.plans.edit', compact('plan', 'featuresText'));
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:landlord.platform_plans,slug,{$plan->id}",
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'max_users' => 'nullable|integer|min:1',
            'max_products' => 'nullable|integer|min:1',
            'max_clients' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
            'max_branches' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        // Process features text into array
        if ($request->filled('features_text')) {
            $features = array_filter(array_map('trim', explode("\n", $request->features_text)));
            $validated['features'] = array_values($features);
        } else {
            $validated['features'] = [];
        }

        $plan->update($validated);

        LandlordAuditLog::log('plan_update', "Plan d'abonnement mis à jour : {$plan->name}", null, ['plan_id' => $plan->id]);

        return redirect()->route('landlord.plans.index')->with('success', "Le plan d'abonnement a été mis à jour.");
    }
}
