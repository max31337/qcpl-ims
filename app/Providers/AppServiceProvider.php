<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Asset;
use App\Policies\AssetPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Asset Policy
        Gate::policy(Asset::class, AssetPolicy::class);

        // Register route middleware alias for role checks
        \Illuminate\Support\Facades\Route::aliasMiddleware('check.role', \App\Http\Middleware\CheckRole::class);
    }
}
