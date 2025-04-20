<?php
use Illuminate\Support\Facades\Route;
use Plugins\RibbonPlugin\Http\Controllers\RibbonSettingsController;

Route::middleware(['web','auth'])
     ->prefix('admin')
     ->group(function(){
         
    // show the settings form
    Route::get('ribbon-settings', [RibbonSettingsController::class,'index'])
         ->name('ribbon-plugin.settings.index');

    // handle the form POST
    Route::post('ribbon-settings', [RibbonSettingsController::class,'update'])
         ->name('ribbon-plugin.settings.update');
});
