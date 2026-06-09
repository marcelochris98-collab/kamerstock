<?php

namespace App\Http\Controllers;

use App\Models\CreditSetting;
use Illuminate\Http\Request;

class CreditSettingController extends Controller
{
    public function edit()
    {
        if (!auth()->user()->hasPermission('settings.manage')) {
            abort(403);
        }

        $settings = CreditSetting::current();

      return view('admin.settings.credit', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->hasPermission('settings.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'min_sales' => 'required|integer|min:1|max:1000',
            'min_months' => 'required|integer|min:0|max:120',
            'min_score' => 'required|integer|min:0|max:100',

            'regular_coefficient' => 'required|numeric|min:0|max:10',
            'loyal_coefficient' => 'required|numeric|min:0|max:10',
            'premium_coefficient' => 'required|numeric|min:0|max:10',

            'max_credit_limit' => 'required|numeric|min:0',

            'allow_regular' => 'nullable|boolean',
            'allow_loyal' => 'nullable|boolean',
            'allow_premium' => 'nullable|boolean',
            'allow_high_risk' => 'nullable|boolean',
            'allow_admin_exception' => 'nullable|boolean',
        ]);

        $data['allow_regular'] = $request->boolean('allow_regular');
        $data['allow_loyal'] = $request->boolean('allow_loyal');
        $data['allow_premium'] = $request->boolean('allow_premium');
        $data['allow_high_risk'] = $request->boolean('allow_high_risk');
        $data['allow_admin_exception'] = $request->boolean('allow_admin_exception');

        CreditSetting::current()->update($data);

        return back()
            ->with('success', 'Paramètres du crédit intelligent mis à jour.')
            ->with('toast_notifications', [
                [
                    'type' => 'success',
                    'title' => 'Paramètres crédit',
                    'message' => 'Les règles de crédit intelligent ont été mises à jour.',
                    'sound' => true,
                ],
            ]);
    }
}