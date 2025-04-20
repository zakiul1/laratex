<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\View\ThemeViewFinder;
use App\Services\PluginLoader;
use App\Models\Plugin;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1) Override the view finder for theming
        $this->app->singleton('view.finder', function($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
        });

        // DON'T touch plugins here—no DB calls in register()
    }

    public function boot(): void
    {
        // If the plugins table doesn't exist yet, bail out
        if (! Schema::hasTable('plugins')) {
            return;
        }

        // 1) Discover any new plugin.json → plugins table
        app(PluginLoader::class)->sync();

        // 2) Register each enabled plugin's service provider
        Plugin::where('enabled', true)->pluck('slug')->each(function($slug) {
            $path     = base_path("plugins/{$slug}/plugin.json");
            if (! File::exists($path)) {
                return;
            }
            $meta     = json_decode(File::get($path), true);
            $provider = $meta['provider'] ?? null;

            if ($provider && class_exists($provider)) {
                // Laravel will run both register() and boot() on this provider immediately
                $this->app->register($provider);
            }
        });

        // 3) In console only: run plugin migrations & publish assets
        if ($this->app->runningInConsole()) {
            app(PluginLoader::class)->bootEnabled();
        }
    }
}
