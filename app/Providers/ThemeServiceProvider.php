<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\View;
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


        // Share theme settings to all views
        View::composer('*', function ($view) {
            $theme = getActiveTheme();
            $settings = ThemeSetting::firstOrCreate(
                ['theme' => $theme],
                ['options' => []]
            );
            $view->with('themeSettings', $settings);
        });

        add_filter('front_header_menu', function () {
            // grab the “header” menu container
            $menu = \App\Models\Menu::where('location', 'header')->first();

            if (!$menu) {
                return collect();
            }

            // eager‑load first level + children
            return $menu->items()->with('children')->get();
        });


        //category show
        if (function_exists('add_filter')) {
            add_filter('the_content', function (string $content) {
                return preg_replace_callback(
                    // match [shop_categories]
                    '/\[featured_categories\]/',
                    function () {
                        // 1) find the “Featured Categories” parent
                        $parent = Category::where('name', 'Featured Categories')->first();
                        if (!$parent) {
                            return '';
                        }

                        // 2) build the query for its children
                        $query = $parent->children();

                        if (Schema::hasColumn('categories', 'is_active')) {
                            $query->where('is_active', true);
                        }
                        if (Schema::hasColumn('categories', 'sort_order')) {
                            $query->orderBy('sort_order', 'asc');
                        }

                        $categories = $query->get();

                        // 3) render the partial
                        $theme = getActiveTheme();
                        return view("themes.{$theme}.partials.featured-categories", compact('categories'))
                            ->render();
                    },
                    $content
                );
            });
        }





        // only hook if the add_filter function exists
        if (function_exists('add_filter')) {
            add_filter('the_content', function (string $content) {
                return preg_replace_callback(
                    // match [featured_products] or [featured_products count="8"]
                    '/\[featured_products(?:\s+count=(["\']?)(\d+)\1)?\]/',
                    function (array $m) {
                        $count = isset($m[2]) ? (int) $m[2] : 12;

                        // fetch the “Featured Products” category
                        $cat = Category::where('name', 'Featured Products')->first();
                        if (!$cat) {
                            return '';
                        }

                        $products = $cat->products()->take($count)->get();

                        // render the theme’s partial and return its HTML
                        $theme = getActiveTheme();
                        return view("themes.{$theme}.partials.featured-products", compact('products'))
                            ->render();
                    },
                    $content
                );
            });
        }

    }
}