<?php

use Illuminate\Support\Facades\Route;
use Plugins\SliderPlugin\Http\Controllers\SliderController;


Route::middleware(['auth', 'web'])
    ->prefix('admin/plugins/slider')
    ->name('slider-plugin.sliders.')
    ->group(function () {
        // List
        Route::get('/', [SliderController::class, 'index'])->name('index');

        // Create
        Route::get('create', [SliderController::class, 'create'])->name('create');
        Route::post('/', [SliderController::class, 'store'])->name('store');

        // Edit / Update
        Route::get('{id}/edit', [SliderController::class, 'edit'])->name('edit');
        Route::put('{id}', [SliderController::class, 'update'])->name('update');

        // Delete entire slider
        Route::delete('{id}', [SliderController::class, 'destroy'])->name('destroy');

        // ─── NEW! Remove a slide’s image ───
        Route::delete(
            '{slider}/item/{item}/image',
            [SliderController::class, 'destroyItemImage']
        )->name('item.image.destroy');
    });