<?php

add_action('before_header', function () {
    echo view('partials.top-banner');
});

add_filter('page_title', function ($title) {
    return '🔥 ' . $title;
});