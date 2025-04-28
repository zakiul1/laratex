<?php

use Illuminate\Support\Facades\Route;
use Plugins\SeoSearchPro\Controllers\SeoSearchController;

/**
 * Plugin: SEO Search Pro
 *
 * Here you can register any web routes needed by your plugin.
 * For example, a preview endpoint for shortcode rendering.
 */

Route::group(['middleware' => ['web']], function () {
    // Preview endpoint for seopost shortcode (optional)
    Route::get('seosearch-pro/preview', function () {
        return view('seosearch::search', []);
    })->name('seosearch.preview');

    // Shortcode‐builder UI
    Route::get('seosearch-pro/builder', [SeoSearchController::class, 'builder'])
        ->name('seosearch.builder');
});