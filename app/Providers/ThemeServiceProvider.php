<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind custom view finder for theming
        $this->app->singleton('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            $theme = env('ACTIVE_THEME', 'default');

            // Prepend the active theme folder path
            $themePath = resource_path("views/themes/{$theme}");
            array_unshift($paths, $themePath);

            return new FileViewFinder(new Filesystem, $paths);
        });
    }

    public function boot()
    {
        // You may put boot logic here later if needed
    }
}