<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RibbonController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SliderImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('layouts.dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Admin routes with auth
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('ribbons', RibbonController::class)->names('ribbons');
    /*  Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard'); */

    Route::resource('posts', PostController::class)->names('posts');
    // Slider main CRUD
    Route::resource('sliders', SliderController::class);

    // Image upload and delete
    Route::post('/slider-images/upload/{slider}', [SliderImageController::class, 'upload'])->name('slider-images.upload');
    Route::delete('/slider-images/{id}', [SliderImageController::class, 'destroy'])->name('slider-images.destroy');
    Route::delete('/sliders/{slider}', [SliderController::class, 'destroy'])->name('sliders.destroy');
    //pages
    Route::resource('pages', PageController::class);

    //Category
    Route::resource('categories', CategoryController::class);

    //Products
    Route::resource('products', ProductController::class);
    Route::delete('/admin/product-images/{id}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');

});

require __DIR__ . '/auth.php';