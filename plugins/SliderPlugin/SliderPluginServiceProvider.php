<?php

namespace Plugins\SliderPlugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Plugins\SliderPlugin\Models\Slider;

class SliderPluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // 1) Where our plugin’s migrations live
        $migrationPath = __DIR__ . '/database/migrations';

        // 2) Tell Laravel to include them when you run "php artisan migrate"
        $this->loadMigrationsFrom($migrationPath);

        // 3) After the app is fully booted, if the "sliders" table doesn't exist,
        //    run only our plugin migrations right now.
        $this->app->booted(function () use ($migrationPath) {
            if (! Schema::hasTable('sliders')) {
                Artisan::call('migrate', [
                    // realpath → absolute path resolution
                    '--path'     => realpath($migrationPath),
                    '--realpath' => true,
                    '--force'    => true,
                ]);
            }
        });

        // 4) Load our admin & front‐end views
        $this->loadViewsFrom(__DIR__ . '/resources/views/admin', 'slider-plugin');
        $this->loadViewsFrom(__DIR__ . '/resources/views/front', 'slider-plugin-front');

        // 5) Load our routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 6) Hook into the filter system, if available
        if (function_exists('add_filter')) {
            // a) Admin sidebar injector
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'Slider Plugin',
                    'route' => 'slider-plugin.sliders.index',
                    'icon'  => 'lucide-images',
                ];
                return $items;
            });

            // b) Front‐end slider injections by location
            foreach (['header', 'footer', 'sidebar'] as $location) {
                add_filter("slider.{$location}", function (string $html) use ($location) {
                    $slider = Slider::where('is_active', true)
                        ->where('location', $location)
                        ->latest('created_at')
                        ->with('items')
                        ->first();

                    if (! $slider) {
                        return $html;
                    }

                    $sliderHtml = view('slider-plugin-front::slider', [
                        'sliders' => collect([$slider]),
                    ])->render();

                    return $html . $sliderHtml;
                });
            }
        }
    }
}
