<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
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
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $settings = Setting::first();
        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $settings->update($data);

        ActivityLog::record('settings.update', 'Paramètres système mis à jour');

        return back()->with('success', ' Paramètres mis à jour !');
    }
}