<?php

use Illuminate\Support\Facades\Route;
use Plugins\DynamicGrid\Controllers\DynamicGridController;

// Admin routes for the Dynamic Grid shortcode builder
Route::middleware(['web', 'auth'])
    ->prefix('admin/plugins/dynamicgrid')
    ->name('admin.dynamicgrid.')
    ->group(function () {
        Route::get('/builder', [DynamicGridController::class, 'builderForm'])
            ->name('builder');

        Route::post('/builder/generate', [DynamicGridController::class, 'generateShortcode'])
            ->name('generate');

        // ← rename this to “categories” so we can easily reference it
        Route::get('/categories/{taxonomy}', [DynamicGridController::class, 'getCategories'])
            ->name('categories');
    });

// Public endpoint for submitting the “Request Price” form
Route::middleware(['web'])
    ->post('/dynamicgrid/request-price', [DynamicGridController::class, 'requestPrice'])
    ->name('dynamicgrid.request-price');