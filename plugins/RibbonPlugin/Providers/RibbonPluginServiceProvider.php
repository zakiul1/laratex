<?php
namespace Plugins\RibbonPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Plugins\RibbonPlugin\Models\RibbonSetting;

class RibbonPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 0) Bail if we’re in HTTP before the plugins table exists
        //    (you may already have this guard in AppServiceProvider)
        
        // 1) Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/web.php');
    
        // 2) Load views (so your settings page shows)
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ribbon-plugin');
    
        // 3) Add sidebar menu
        add_filter('admin_sidebar_menu', function(array $items) {
            $items[] = [
                'label' => 'Ribbon Settings',
                'icon'  => 'lucide-ribbon',
                'route' => 'ribbon-plugin.settings.index',
            ];
            return $items;
        });
    
        // 4) Frontend ribbon hook (as before)
        add_filter('frontend_ribbon', function() {
            $setting = \Plugins\RibbonPlugin\Models\RibbonSetting::first();
            return view('ribbon-plugin::ribbon', compact('setting'))->render();
        });
    }
    
}
