<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\ViewComposers\HeaderComposer;
use App\Http\ViewComposers\SidebarComposer;
use App\Http\ViewComposers\ClientPortalComposer;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.header', HeaderComposer::class);
        View::composer('layouts.sidebar', SidebarComposer::class);
        View::composer('layouts.client-portal', ClientPortalComposer::class);
    }
}
