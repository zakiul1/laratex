<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\HookManager;

class HookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HookManager::class, function () {
            return new HookManager();
        });
    }

    public function boot(): void
    {
        // Auto-load hooks from active theme
        $theme = getActiveTheme();
        $hookPath = resource_path("views/themes/{$theme}/hooks.php");

        if (file_exists($hookPath)) {
            require_once $hookPath;
        }

        // Optional: plugin hooks loader
        // foreach (glob(base_path('plugins/*/hooks.php')) as $pluginHook) {
        //     require_once $pluginHook;
        // }
    }
}