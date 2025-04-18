<?php


add_filter('page_title', function ($title) {
    $customTitle = plugin_setting('seo-plugin', 'meta_title', null);

    return $customTitle ?: $title;
});