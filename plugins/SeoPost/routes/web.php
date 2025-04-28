<?php

use Illuminate\Support\Facades\Route;
use Plugins\SeoPost\Http\Controllers\SeoPostController;

Route::middleware(['web', 'auth',])
    ->prefix('admin/plugins/seopost')
    ->name('seopost.')
    ->group(function () {
        // Show the configuration form
        Route::get('config', [SeoPostController::class, 'configForm'])->name('config');

        // Generate and return the shortcode
        Route::post('generate', [SeoPostController::class, 'generate'])->name('generate');
    });