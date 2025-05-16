<?php

use Illuminate\Support\Facades\Route;
use Plugins\DynamicGrid\Controllers\DynamicGridController;

Route::middleware(['web', 'auth',])
    ->prefix('admin/plugins/dynamicgrid')
    ->name('admin.dynamicgrid.')
    ->group(function () {
        Route::get('/builder', [DynamicGridController::class, 'builderForm'])->name('builder');
        Route::post('/builder/generate', [DynamicGridController::class, 'generateShortcode'])->name('generate');

        // AJAX for categories dropdown
        Route::get('/categories/{taxonomy}', [DynamicGridController::class, 'getCategories'])
            ->name('builder.categories');


    });