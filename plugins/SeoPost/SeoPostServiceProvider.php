<?php

namespace Plugins\SeoPost;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SeoPostServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config as before…
        $this->mergeConfigFrom(__DIR__ . '/config/seopost.php', 'seopost');
    }

    public function boot()
    {
        // Auto‐load & run migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views/admin', 'admin');
        $this->app->booted(function () {
            if (!Schema::hasTable('seopost_settings')) {
                Artisan::call('migrate', [
                    '--path' => realpath(__DIR__ . '/database/migrations'),
                    '--realpath' => true,
                    '--force' => true,
                ]);
            }
        });

        // Load views & routes
        $this->loadViewsFrom(__DIR__ . '/resources/views/shortcode', 'seopost');
        $this->loadViewsFrom(__DIR__ . '/resources/views/admin', 'seopost-admin');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // If the global add_filter helper exists, use it:
        if (function_exists('add_filter')) {
            // 1) Inject “SeoPost” into the admin sidebar
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'SeoPost',
                    'route' => 'seopost.config',   // your generator UI
                    'icon' => 'lucide-search',    // Lucide icon
                ];
                return $items;
            });

            /*     // 2) Hook into the shortcode render pipeline
                add_filter('render_shortcode', function (string $output, string $tag, array $attrs) {
                    if ($tag === 'seopost') {
                        return (new Shortcodes\SeoPostShortcode())->render($attrs);
                    }
                    return $output;
                }, 10, 3); */
        }



        if (function_exists('add_filter')) {
            // 1) Inject into the main page content
            add_filter('seopost_content', function (string $html) {
                // find [tag attr="…"] and let your existing render_shortcode filter handle it
                return preg_replace_callback('/\[(\w+)([^\]]*)\]/', function ($match) {
                    $tag = $match[1];
                    $rawAttrs = trim($match[2]);

                    // parse attrs into [ key => value ]
                    preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $rawAttrs, $m, PREG_SET_ORDER);
                    $attrs = [];
                    foreach ($m as $kv) {
                        $attrs[$kv[1]] = $kv[2];
                    }

                    // now run your render_shortcode filter
                    return apply_filters('render_shortcode', '', $tag, $attrs);
                }, $html);
            });
        }
    }
}