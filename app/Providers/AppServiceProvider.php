<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Asset;
use App\Policies\AssetPolicy;
use Illuminate\Support\Facades\Blade;

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

        // Register UI blade view component aliases (so templates can use <x-ui-card> etc.)
        $uiComponents = [
            'card', 'card-title', 'card-description', 'button', 'icon', 'input', 'select', 'modal', 'label', 'badge', 'alert', 'file-upload', 'textarea',
            'table', 'table-row', 'table-header', 'table-head', 'table-cell', 'table-body'
        ];

        foreach ($uiComponents as $c) {
            // map e.g. 'card' -> components.ui.card with alias 'ui-card'
            $viewPath = 'components.ui.' . str_replace('-', '.', $c);
            Blade::component($viewPath, 'ui-' . $c);
        }
    }
}
