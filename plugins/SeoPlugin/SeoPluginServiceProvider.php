<?php

namespace Plugins\SeoPlugin;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PluginLifecycleInterface;

class SeoPluginServiceProvider extends ServiceProvider implements PluginLifecycleInterface
{
    public function register(): void
    {
    }

    public function boot(): void
    {


        $title = plugin_setting('seo-plugin', 'meta_title', 'Default Title');

        view()->composer('*', function ($view) use ($title) {
            $view->with('seoTitle', $title);
        });


        // Load routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        // Load hooks
        if (file_exists(__DIR__ . '/hooks.php')) {
            require_once __DIR__ . '/hooks.php';
        }

        // Load views (optional)
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'seo');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');


        // Load translations (optional)
        $this->publishes([
            __DIR__ . '/public' => public_path('vendor/seo-plugin'),
        ], 'seo-plugin-assets'); // match slug



    }

    public function install(): void
    {
        \Artisan::call('migrate', [
            '--path' => 'plugins/SeoPlugin/database/migrations',
            '--force' => true
        ]);
    }


    public function activate(): void
    {
        // Triggered when plugin is enabled from admin
        logger('SEO Plugin Activated');
    }

    public function deactivate(): void
    {
        // Triggered when plugin is disabled from admin
        logger('SEO Plugin Deactivated');
    }
    public function uninstall(): void
    {
        // Drop tables, clear configs, etc.
        // Optional: \Schema::dropIfExists('seo_logs');
        logger('SEO Plugin uninstalled.');
    }
}