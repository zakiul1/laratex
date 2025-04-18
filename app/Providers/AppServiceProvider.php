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
        // ✅ Only sync plugins once during register (safe before DB bootstrapping)


        // 🔧 Bind theme view finder (safe — no DB access)
        $this->app->bind('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
        });

        // ⚠️ DO NOT register plugin providers here (bootEnabled handles it safely in boot)
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(PluginLoader::class)->sync();
        // ✅ Prevent plugin loading if DB isn't ready or in CLI (migrations, tinker etc.)
        if (app()->runningInConsole() || !Schema::hasTable('plugins')) {
            return;
        }

        // ✅ Register all enabled plugins from DB
        app(PluginLoader::class)->bootEnabled();
    }
}