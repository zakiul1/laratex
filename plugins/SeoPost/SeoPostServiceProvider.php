<?php

namespace Plugins\SeoPost;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Services\Shortcode;
use Plugins\SeoPost\SeoPostShortcode;

class SeoPostServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge our default shortcode config
        $this->mergeConfigFrom(__DIR__ . '/config/seopost.php', 'seopost');
    }

    public function boot()
    {
        // 1) Load & auto-run migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->app->booted(function () {
            if (!Schema::hasTable('seopost_settings')) {
                Artisan::call('migrate', [
                    '--path' => realpath(__DIR__ . '/database/migrations'),
                    '--realpath' => true,
                    '--force' => true,
                ]);
            }
        });

        // 2) Load views
        // Front-end shortcode templates:
        $this->loadViewsFrom(__DIR__ . '/resources/views/shortcode', 'seopost');
        // Admin UI:
        $this->loadViewsFrom(__DIR__ . '/resources/views/admin', 'seopost-admin');

        // 3) Load routes for admin UI
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 4) Inject “SeoPost” menu item into sidebar (if helper exists)
        if (function_exists('add_filter')) {
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'SeoPost',
                    'route' => 'seopost.config',
                    'icon' => 'lucide-search',
                ];
                return $items;
            });

            // 5) Register your shortcode handler
            Shortcode::add('seopost', [SeoPostShortcode::class, 'handle']);

            // 6) Parse all shortcodes in content
            add_filter('seopost_content', function (string $html) {
                return Shortcode::compile($html);
            });
        }
    }
}