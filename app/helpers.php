<?php

use App\Models\Menu;
use App\Models\PluginSetting;
use App\Models\SiteSetting;
use App\Models\ThemeSetting;
use App\Support\HookManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// =============================
// 🔹 Theme Functions
// =============================

if (!function_exists('getActiveTheme')) {
    function getActiveTheme(): string
    {
        try {
            if (app()->runningInConsole() || !Schema::hasTable('site_settings')) {
                return 'default';
            }

            $setting = SiteSetting::first();
            if ($setting && !empty($setting->active_theme)) {
                return $setting->active_theme;
            }
        } catch (\Throwable $e) {
            // Silently fail
        }

        return 'default';
    }
}

if (!function_exists('getThemeSetting')) {
    function getThemeSetting(): ?ThemeSetting
    {
        try {
            $theme = getActiveTheme();

            if (app()->runningInConsole() || !Schema::hasTable('theme_settings')) {
                return null;
            }

            return ThemeSetting::where('theme', $theme)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('theme_settings')) {
    function theme_settings()
    {
        return getThemeSetting();
    }
}

if (!function_exists('site_settings')) {
    function site_settings()
    {
        try {
            if (app()->runningInConsole() || !Schema::hasTable('site_settings')) {
                return null;
            }

            return SiteSetting::first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('getThemeTemplates')) {
    function getThemeTemplates()
    {
        $theme = getActiveTheme();
        $path = resource_path("views/themes/{$theme}/theme.json");

        if (!File::exists($path)) {
            return [];
        }

        $json = json_decode(File::get($path), true);

        return $json['templates'] ?? [];
    }
}

if (!function_exists('theme_widget_areas')) {
    function theme_widget_areas(): array
    {
        return [
            'footer' => 'Footer',
            'sidebar' => 'Sidebar',
            'header' => 'Header',
        ];
    }
}

// =============================
// 🔹 Menu Functions
// =============================

if (!function_exists('get_menu_by_slug')) {
    function get_menu_by_slug($slug)
    {
        try {
            if (app()->runningInConsole() || !Schema::hasTable('menus')) {
                return null;
            }

            return Menu::with('items')->where('slug', $slug)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

// =============================
// 🔹 Widget Rendering
// =============================

if (!function_exists('render_widgets')) {
    function render_widgets($area)
    {
        try {
            if (app()->runningInConsole() || !Schema::hasTable('widgets')) {
                return '';
            }

            $widgets = \App\Models\Widget::where('widget_area', $area)
                ->where('status', true)
                ->orderBy('order')
                ->get();

            return view('components.widget-area', compact('widgets'))->render();
        } catch (\Throwable $e) {
            return '';
        }
    }
}

// =============================
// 🔹 Hook System
// =============================

if (!function_exists('hooks')) {
    function hooks(): HookManager
    {
        return app(HookManager::class);
    }
}

if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        hooks()->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, ...$args): void
    {
        hooks()->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        hooks()->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, $value, ...$args)
    {
        return hooks()->applyFilters($hook, $value, ...$args);
    }
}


// =============================
// 🔹 Plugin Settings System
// =============================


if (!function_exists('plugin_setting')) {
    function plugin_setting(string $pluginSlug, string $key, $default = null)
    {
        return PluginSetting::where('plugin_slug', $pluginSlug)
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}

if (!function_exists('set_plugin_setting')) {
    function set_plugin_setting(string $pluginSlug, string $key, $value): void
    {
        PluginSetting::updateOrCreate(
            ['plugin_slug' => $pluginSlug, 'key' => $key],
            ['value' => $value]
        );
    }
}