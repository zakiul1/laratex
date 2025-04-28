<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Shortcode;

class ShortcodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the 'shortcode' key to our Shortcode class
        $this->app->singleton('shortcode', fn() => new Shortcode());
    }

    public function boot(): void
    {
        //
    }
}