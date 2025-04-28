<?php

namespace Plugins\SeoSearchPro;

use Illuminate\Support\ServiceProvider;
use App\Support\HookManager;
use App\Facades\Shortcode;                              // ← correct import
use Plugins\SeoSearchPro\Controllers\SeoSearchController;

class SeoSearchProServiceProvider extends ServiceProvider
{
    /**
     * Register plugin routes, views, migrations.
     */
    public function register(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'seosearch');
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    /**
     * Bootstrap hook‐based shortcode registration and asset publishing.
     */
    public function boot(): void
    {
        /** @var HookManager $hooks */
        $hooks = app(HookManager::class);

        // Delay shortcode registration until the 'init' hook fires
        $hooks->addAction('init', function () {
            Shortcode::add('seopost', function (array $attrs = []) {
                $ctrl = app(SeoSearchController::class);
                $sanitized = $ctrl->sanitizeAttributes($attrs);
                $posts = $ctrl->queryPosts($sanitized);

                return view('seosearch::search', [
                    'posts' => $posts,
                    'attrs' => $sanitized,
                ])->render();
            });
        });

        // Publish the CSS to public/vendor/seosearch-pro
        $this->publishes([
            __DIR__ . '/resources/assets/css/seosearch-pro.css' =>
                public_path('vendor/seosearch-pro/seosearch-pro.css'),
        ], 'public');
    }
}