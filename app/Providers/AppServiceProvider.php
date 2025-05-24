<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use App\View\ThemeViewFinder;
use App\Services\PluginLoader;
use App\Models\Plugin;
use App\Support\HookManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1) Override the view finder for theming
        $this->app->singleton('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
        });


        $this->app->bind(
            \Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator::class,
            \App\MediaLibrary\PublicUrlGenerator::class
        );
        // 2) Bind the HookManager so plugins can register actions/filters
        $this->app->singleton(HookManager::class, fn() => new HookManager());
    }

    public function boot(): void
    {
        // If the plugins table doesn't exist yet, bail out
        if (!Schema::hasTable('plugins')) {
            return;
        }

        // 1) Discover any new plugin.json → plugins table
        app(PluginLoader::class)->sync();

        // 2) Register each enabled plugin's service provider
        Plugin::where('enabled', true)
            ->pluck('provider')
            ->each(
                fn($provider) => class_exists($provider)
                ? $this->app->register($provider)
                : null
            );

        // 3) Trigger the 'init' hook so plugins can register actions/shortcodes
        app(HookManager::class)->doAction('init');

        // 4) Register the @seopost Blade directive
        Blade::directive('seopost', function ($expression) {
            return "<?php echo \\App\\Services\\Shortcode::compile($expression); ?>";
        });

        // 5) In console only: run plugin migrations & publish assets
        if ($this->app->runningInConsole()) {
            app(PluginLoader::class)->bootEnabled();
        }
    }
}