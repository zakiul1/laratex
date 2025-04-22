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

        // 2) Tell Laravel about them
        $this->loadMigrationsFrom($migrationPath);

        // 3) If the 'sliders' table doesn't exist yet, run those migrations now
        if (!Schema::hasTable('sliders')) {
            Artisan::call('migrate', [
                '--path' => 'Plugins/SliderPlugin/database/migrations',
                '--force' => true,
            ]);
        }

        // 4) Load our views
        $this->loadViewsFrom(__DIR__ . '/resources/views/admin', 'slider-plugin');
        $this->loadViewsFrom(__DIR__ . '/resources/views/front', 'slider-plugin-front');

        // 5) Load our routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if (function_exists('add_filter')) {
            // a) Add to admin sidebar
            add_filter('admin_sidebar_menu', function (array $items) {
                $items[] = [
                    'label' => 'Slider Plugin',
                    'route' => 'slider-plugin.sliders.index',
                    'icon' => 'lucide-images',
                ];
                return $items;
            });

            // b) Inject the latest active slider per location
            foreach (['header', 'footer', 'sidebar'] as $location) {
                add_filter("slider.{$location}", function (string $html) use ($location) {
                    $slider = Slider::where('is_active', true)
                        ->where('location', $location)
                        ->latest('created_at')
                        ->with('items')
                        ->first();

                    if (!$slider) {
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