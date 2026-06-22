<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupWizardController extends Controller
{
    /**
     * Show the Setup Wizard.
     */
    public function index(Request $request)
    {
        $settings = Setting::firstOrCreate(['id' => 1]);

        // Get selected business type (from query string or setting, fallback to quincaillerie)
        $businessType = $request->input('business_type', $settings->business_type ?? 'quincaillerie');
        $config = config("business_types.{$businessType}");
        if (!$config) {
            $businessType = 'quincaillerie';
            $config = config("business_types.quincaillerie");
        }

        // Recommended Categories status check
        $recommendedCategories = $config['default_categories'] ?? ['Général'];
        $categories = [];
        foreach ($recommendedCategories as $name) {
            $exists = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists();
            $categories[] = [
                'name' => $name,
                'exists' => $exists,
            ];
        }

        // Units mapping & detection
        $allUnits = [
            'piece'   => 'Pièce(s)',
            'metre'   => 'Mètre(s)',
            'kg'      => 'Kg',
            'litre'   => 'Litre(s)',
            'boite'   => 'Boîte(s)',
            'sachet'  => 'Sachet(s)',
            'carton'  => 'Carton(s)',
            'paquet'  => 'Paquet(s)',
            'flacon'  => 'Flacon(s)',
            'tube'    => 'Tube(s)',
            'kit'     => 'Kit(s)',
            'lot'     => 'Lot(s)',
            'palette' => 'Palette(s)',
            'sac'     => 'Sac(s)',
        ];

        $unitMap = [
            'pièce'   => 'piece',
            'mètre'   => 'metre',
            'kg'      => 'kg',
            'litre'   => 'litre',
            'boîte'   => 'boite',
            'sachet'  => 'sachet',
            'carton'  => 'carton',
            'paquet'  => 'paquet',
            'flacon'  => 'flacon',
            'tube'    => 'tube',
            'kit'     => 'kit',
            'lot'     => 'lot',
            'palette' => 'palette',
            'sac'     => 'sac',
        ];

        $recommendedUnitsStr = $config['default_units'] ?? 'pièce, kg, litre, boîte, sachet';
        $recommendedUnitsArr = array_map('trim', explode(',', $recommendedUnitsStr));
        $recommendedUnits = [];
        foreach ($recommendedUnitsArr as $u) {
            $lowerU = mb_strtolower($u);
            if (isset($unitMap[$lowerU])) {
                $recommendedUnits[] = $unitMap[$lowerU];
            }
        }

        return view('admin.setup.index', compact('settings', 'businessType', 'config', 'categories', 'allUnits', 'recommendedUnits'));
    }

    /**
     * Store settings values, select units and create categories.
     */
    public function store(Request $request)
    {
        $request->validate([
            'shop_name'            => 'required|string|max:100',
            'address'              => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'currency'             => 'required|string|max:10',
            'tax_rate'             => 'required|numeric|min:0|max:100',
            'invoice_prefix'       => 'required|string|max:10',
            'logo'                 => 'nullable|image|mimes:png,jpg,jpeg,webp,gif,svg|max:4096',
            'business_type'        => 'required|string|in:quincaillerie,boutique_generale,superette,pieces_detachees,cosmetique,pharmacie_parapharmacie,informatique,electromenager,depot_grossiste,autre',
            'business_type_custom' => 'nullable|required_if:business_type,autre|string|max:100',
            'categories'           => 'nullable|array',
            'categories.*'         => 'string|max:100',
            'units'                => 'nullable|array',
            'units.*'              => 'string|in:piece,metre,kg,litre,boite,sachet,carton,paquet,flacon,tube,kit,lot,palette,sac',
        ]);

        $settings = Setting::firstOrCreate(['id' => 1]);
        $data = $request->only([
            'shop_name', 'address', 'phone', 'email', 'currency', 'tax_rate', 'invoice_prefix',
            'business_type', 'business_type_custom'
        ]);

        // Logo upload handling
        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Categorisation setup
        $recommended = config("business_types.{$request->business_type}.default_categories", []);
        $selectedCategories = $request->input('categories', []);
        $createdCount = 0;
        foreach ($selectedCategories as $categoryName) {
            // Safety verification: only create categories suggested for this type
            if ($request->business_type !== 'autre' && !in_array($categoryName, $recommended)) {
                continue;
            }

            $exists = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])->exists();
            if (!$exists) {
                Category::create([
                    'name' => $categoryName,
                ]);
                $createdCount++;
            }
        }

        // Enabled units mapping
        $selectedUnits = $request->input('units', []);
        if (empty($selectedUnits)) {
            // Default recommendations if empty
            $unitMap = [
                'pièce'   => 'piece',
                'mètre'   => 'metre',
                'kg'      => 'kg',
                'litre'   => 'litre',
                'boîte'   => 'boite',
                'sachet'  => 'sachet',
                'carton'  => 'carton',
                'paquet'  => 'paquet',
                'flacon'  => 'flacon',
                'tube'    => 'tube',
                'kit'     => 'kit',
                'lot'     => 'lot',
                'palette' => 'palette',
                'sac'     => 'sac',
            ];
            $configUnits = explode(',', config("business_types.{$request->business_type}.default_units", 'pièce, kg, litre, boîte, sachet'));
            foreach ($configUnits as $u) {
                $lowerU = mb_strtolower(trim($u));
                if (isset($unitMap[$lowerU])) {
                    $selectedUnits[] = $unitMap[$lowerU];
                }
            }
            if (empty($selectedUnits)) {
                $selectedUnits = ['piece', 'kg', 'litre', 'boite', 'sachet'];
            }
        }
        $data['enabled_units'] = $selectedUnits;
        $data['setup_step'] = 'configured';

        $settings->update($data);

        ActivityLog::record('settings.setup-wizard-saved', "Paramètres enregistrés et {$createdCount} catégories créées via l'assistant");

        return back()->with('success', "Paramètres enregistrés avec succès ! Vous pouvez maintenant finaliser la configuration.");
    }

    /**
     * Complete the setup wizard.
     */
    public function finish(Request $request)
    {
        $settings = Setting::firstOrCreate(['id' => 1]);
        
        $settings->update([
            'setup_completed' => true,
            'setup_completed_at' => now(),
            'setup_step' => 'completed',
        ]);

        ActivityLog::record('settings.setup-wizard-finished', "Assistant de configuration initiale complété");

        return redirect()->route('dashboard')->with('success', "Configuration initiale terminée avec succès.");
    }

    /**
     * Reset the setup wizard.
     */
    public function reset()
    {
        $settings = Setting::firstOrCreate(['id' => 1]);

        $settings->update([
            'setup_completed' => false,
            'setup_completed_at' => null,
            'setup_step' => null,
        ]);

        ActivityLog::record('settings.setup-wizard-reset', "Assistant de configuration initiale réinitialisé");

        return redirect()->route('admin.setup.index')->with('success', "L'assistant de configuration a été réinitialisé.");
    }
}
