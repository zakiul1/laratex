<?php

namespace Plugins\RibbonPlugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class RibbonPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 1) Auto-load migrations so "php artisan migrate" includes them
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // 2) If the table doesn't exist, run only our migrations now
        $this->app->booted(function () {
            if (!Schema::hasTable('ribbon_settings')) {
                Artisan::call('migrate', [
                    '--path' => realpath(__DIR__ . '/database/migrations'),
                    '--realpath' => true,
                    '--force' => true,
                ]);
            }
        });

        // 3) Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views/settings', 'ribbon-plugin');
        $this->loadViewsFrom(__DIR__ . '/resources/views/front', 'ribbon-plugin-front');

        // 4) Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 5) Register both of your filters under one guard
        if (function_exists('add_filter')) {
            // Admin sidebar link
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'Ribbon Settings',
                    'route' => 'ribbon-plugin.settings.edit',
                    'icon' => 'lucide-activity',
                ];
                return $items;
            });

            // Front-end injection hook
            add_filter('frontend_ribbon', function (string $html) {
                $ribbonHtml = view('ribbon-plugin-front::ribbon')->render();
                return $ribbonHtml . $html;
            });
        }
    }

    public function register()
    {
        // nothing to register
    }
}