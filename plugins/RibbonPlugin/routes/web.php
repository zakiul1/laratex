<?php
use Illuminate\Support\Facades\Route;
use Plugins\RibbonPlugin\Http\Controllers\RibbonSettingsController;

Route::middleware(['auth', 'web'])
    ->prefix('admin/plugins/ribbon')
    ->name('ribbon-plugin.settings.')
    ->group(function () {
        Route::get('/', [RibbonSettingsController::class, 'edit'])->name('edit');
        Route::post('/', [RibbonSettingsController::class, 'update'])->name('update');
    });