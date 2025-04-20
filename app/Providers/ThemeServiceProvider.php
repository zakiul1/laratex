<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            $theme = 'default';

            if (Schema::hasTable('site_settings')) {
                $settings = SiteSetting::first();
                if ($settings && !empty($settings->active_theme)) {
                    $theme = $settings->active_theme;
                }
            }

            $themePath = resource_path("views/themes/{$theme}");

            if (is_dir($themePath)) {
                array_unshift($paths, $themePath);
            }

            return new FileViewFinder(new Filesystem, $paths);
        });
    }

    public function boot()
    {

        add_filter('front_header_menu', function(){
            // grab the “header” menu container
            $menu = \App\Models\Menu::where('location','header')->first();
        
            if (! $menu) {
                return collect();
            }
        
            // eager‑load first level + children
            return $menu->items()->with('children')->get();
        });
        


    }
}
