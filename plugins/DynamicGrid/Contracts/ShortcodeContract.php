<?php

namespace Plugins\DynamicGrid\Contracts;

/**
 * A contract that any DynamicGrid shortcode handler must implement.
 */
interface ShortcodeContract
{
    /**
     * Render the shortcode into an HTML string.
     *
     * @param  array  $args  Attributes passed to the shortcode
     * @return string        Rendered HTML
     */
    public function render(array $args = []): string;
}