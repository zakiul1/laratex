<?php

use Illuminate\Support\Facades\Route;
use Plugins\SliderPlugin\Http\Controllers\SliderController;

Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->as('slider-plugin.')
    ->group(function () {
        Route::resource('sliders', SliderController::class);
    });