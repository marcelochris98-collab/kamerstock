<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Tenancy\TenantContext::class, function () {
            return new \App\Services\Tenancy\TenantContext();
        });

        $this->app->singleton(\App\Services\Platform\SupportContext::class, function () {
            return new \App\Services\Platform\SupportContext();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
