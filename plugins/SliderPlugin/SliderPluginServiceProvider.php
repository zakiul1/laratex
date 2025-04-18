<?php

namespace Plugins\SliderPlugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SliderPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Load routes from: plugins/SliderPlugin/routes/web.php
        $this->loadRoutesFrom(base_path('plugins/SliderPlugin/routes/web.php'));

        // ✅ Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'slider-plugin');

        // ✅ Inject plugin sidebar menu (via hook system)
        add_filter('admin_sidebar_menu', function (array $items) {
            $items[] = [
                'label' => 'Sliders',
                'icon' => 'lucide-images',
                'children' => [
                    [
                        'label' => 'All Sliders',
                        'route' => 'slider-plugin.sliders.index',
                    ],
                    [
                        'label' => 'Add New',
                        'route' => 'slider-plugin.sliders.create',
                    ],
                ],
            ];
            return $items;
        });
    }

    public function register(): void
    {
        // Optional bindings
    }
}