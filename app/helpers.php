<?php

use App\Models\Menu;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\File;
if (!function_exists('getActiveTheme')) {
    function getActiveTheme(): string
    {
        try {
            if (Schema::hasTable('site_settings')) {
                $setting = SiteSetting::first();

                if ($setting && !empty($setting->active_theme)) {
                    return $setting->active_theme;
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — do nothing
        }

        return 'default';
    }
    if (!function_exists('getThemeSetting')) {
        function getThemeSetting(): ?ThemeSetting
        {
            $activeTheme = getActiveTheme();
            return ThemeSetting::where('theme', $activeTheme)->first();
        }
    }
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
    if (!function_exists('site_settings')) {
        function site_settings()
        {
            return SiteSetting::first();
        }
    }

    if (!function_exists('theme_settings')) {
        function theme_settings()
        {
            $theme = getActiveTheme();
            return ThemeSetting::where('theme', $theme)->first();
        }
    }
    if (!function_exists('theme_widget_areas')) {
        function theme_widget_areas(): array
        {
            // Default widget areas — extend based on theme.json later
            return [
                'footer' => 'Footer',
                'sidebar' => 'Sidebar',
                'header' => 'Header',
            ];
        }
    }

    if (!function_exists('render_widgets')) {
        function render_widgets($area)
        {
            $widgets = \App\Models\Widget::where('widget_area', $area)
                ->where('status', true)
                ->orderBy('order')
                ->get();

            return view('components.widget-area', compact('widgets'))->render();
        }
    }


    if (!function_exists('get_menu_by_slug')) {
        function get_menu_by_slug($slug)
        {
            return Menu::with('items')->where('slug', $slug)->first();
        }
    }
}