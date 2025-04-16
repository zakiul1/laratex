<?php

namespace App\View;

use Illuminate\View\FileViewFinder;
use App\Models\SiteSetting;

class ThemeViewFinder extends FileViewFinder
{
    public function find($view)
    {
        $theme = SiteSetting::activeTheme();
        $themePath = resource_path("themes/{$theme}/views");

        // Prepend the theme path so Laravel will look here first
        $this->prependLocation($themePath);

        return parent::find($view);
    }
}