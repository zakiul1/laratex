<?php

namespace App\Providers;

use App\View\ThemeViewFinder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}