<?php

use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

if (!function_exists('getActiveTheme')) {
    function getActiveTheme()
    {
        if (Schema::hasTable('site_settings')) {
            return SiteSetting::first()->active_theme ?? 'default';
        }

        return 'default';
    }
}
