<?php

namespace Plugins\DynamicGrid;

use Illuminate\Support\ServiceProvider;

class DynamicGridServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/dynamicgrid.php',
            'dynamicgrid'
        );
    }

    public function boot(): void
    {
        // 1) Publish config so users can override defaults
        $this->publishes([
            __DIR__ . '/config/dynamicgrid.php' => config_path('dynamicgrid.php'),
        ], 'dynamicgrid-config');

        // 2) Load admin routes
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');

        // 3) Load views under the "dynamicgrid" namespace
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'dynamicgrid');

        if (function_exists('add_filter')) {
            // 4) Add builder link to admin sidebar
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'Dynamic Grid Builder',
                    'route' => 'admin.dynamicgrid.builder',
                    'icon' => 'lucide-grid',
                ];
                return $items;
            });

            // 5) Register the [dynamicgrid] shortcode handler
            add_filter('shortcode/dynamicgrid', function (array $args = []) {
                return (new DynamicGridShortcode())->render($args);
            });

            // 6) Parse and replace [dynamicgrid …] in content
            add_filter('the_content', function (string $content) {
                return preg_replace_callback(
                    '/\[dynamicgrid\s*([^\]]*)\]/',
                    function ($match) {
                        // extract key="value" pairs
                        preg_match_all(
                            '/([\w_]+)="([^"]*)"/',
                            $match[1],
                            $m,
                            PREG_SET_ORDER
                        );
                        $attrs = [];
                        foreach ($m as $pair) {
                            // normalize any hyphens
                            $key = str_replace('-', '_', $pair[1]);
                            $attrs[$key] = $pair[2];
                        }
                        // render via our shortcode filter
                        return apply_filters('shortcode/dynamicgrid', $attrs);
                    },
                    $content
                );
            });
        }
    }
}