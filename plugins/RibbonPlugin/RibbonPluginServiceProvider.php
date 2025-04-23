<?php

namespace Plugins\RibbonPlugin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;


class RibbonPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
         // only run your plugin’s migrations if the table doesn’t exist
         if (! Schema::hasTable('ribbon_settings')) {
            Artisan::call('migrate', [
                // point at the real migrations folder inside your plugin
                '--path'     => realpath(__DIR__ . '/database/migrations'),
                '--realpath' => true,
                '--force'    => true,
            ]);
        }
        // 1) Auto‑load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // 2) Load Views
        $this->loadViewsFrom(__DIR__ . '/resources/views/settings', 'ribbon-plugin');
        $this->loadViewsFrom(__DIR__ . '/resources/views/front', 'ribbon-plugin-front');

        // 3) Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 4) Admin‑sidebar menu hook (unchanged)
        if (function_exists('add_filter')) {
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'Ribbon Settings',
                    'route' => 'ribbon-plugin.settings.edit',
                    'icon' => 'lucide-activity',
                ];
                return $items;
            });
        }

        // 5) **Front‑end injection via your hook system**  
        //    Replace 'theme.header' with whatever hook your layout fires.
        if (function_exists('add_filter')) {
            add_filter('frontend_ribbon', function (string $html) {
                // prepend the ribbon partial to the existing header HTML
                $ribbonHtml = view('ribbon-plugin-front::ribbon')->render();
                return $ribbonHtml . $html;
            });
        }
    }

    public function register()
    {
        // No uninstall command here—your system handles plugin deletion.
    }
}