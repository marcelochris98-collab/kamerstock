<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::firstOrCreate(['id' => 1]);
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'shop_name'      => 'required|string|max:100',
            'address'        => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'currency'       => 'required|string|max:10',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'invoice_prefix' => 'required|string|max:10',
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg,webp,gif,svg|max:4096',
        ]);

        $settings = Setting::firstOrCreate(['id' => 1]);
        $data = $request->except(['logo', 'remove_logo']);

        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }

            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = null;
        }

        $settings->update($data);

        ActivityLog::record('settings.update', 'Paramètres système mis à jour');

        return back()->with('success', 'Paramètres mis à jour !');
    }
}
