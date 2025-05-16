<?php

namespace Plugins\DynamicGrid;

use Plugins\DynamicGrid\Contracts\ShortcodeContract;

class DynamicGridShortcode implements ShortcodeContract
{
    /**
     * Render the [dynamicgrid] shortcode.
     *
     * @param  array  $args  Associative array of shortcode attributes
     * @return string        Rendered HTML
     */
    public function render(array $args = []): string
    {
        // 1) Merge defaults from config with passed attributes
        $opts = array_merge(config('dynamicgrid'), $args);

        // 2) Map any flat columns_* args into the nested columns array
        foreach ($args as $key => $value) {
            if (preg_match('/^columns_([a-z]+)$/', $key, $m)) {
                // e.g. 'columns_mobile' → $m[1] = 'mobile'
                $opts['columns'][$m[1]] = (int) $value;
            }
        }

        // 3) Determine which type & layout to use
        $type = $opts['type'] ?? 'single_post';
        $layout = $opts['layout'] ?? 'layout1';

        // 4) Render the matching Blade view under resources/views/front/{type}/{layout}.blade.php
        return view("dynamicgrid::front.{$type}.{$layout}", compact('opts'))->render();
    }
}