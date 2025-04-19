<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\View\ThemeViewFinder;
use App\Services\PluginLoader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind our ThemeViewFinder so Blade will look in themes/… first.
        $this->app->singleton('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
        });

        // Do not call PluginLoader here.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // If the plugins table isn't yet migrated, bail out entirely.
        if (!Schema::hasTable('plugins')) {
            return;
        }

        // 1) Sync any newly‐detected plugin manifests into the DB:
        app(PluginLoader::class)->sync();

        // 2) Only in HTTP (non‐Artisan) contexts, actually boot up enabled plugins:
        if (!app()->runningInConsole()) {
            app(PluginLoader::class)->bootEnabled();
        }
    }
}