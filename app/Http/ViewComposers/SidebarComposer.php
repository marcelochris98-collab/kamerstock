<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Setting;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $siteSettings = Setting::first();

        $view->with([
            'siteSettings' => $siteSettings,
        ]);
    }
}
