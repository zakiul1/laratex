<?php

namespace App\View;

use Illuminate\View\FileViewFinder;
use Illuminate\Support\Facades\Schema;

class ThemeViewFinder extends FileViewFinder
{
    public function find($view)
    {
        // ✅ SAFELY get active theme from helper instead of direct model call
        $theme = function_exists('getActiveTheme') ? getActiveTheme() : 'default';

        // ✅ Check if path exists before prepending
        $themePath = resource_path("views/themes/{$theme}");

        if (is_dir($themePath)) {
            $this->prependLocation($themePath);
        }

        return parent::find($view);
    }
}