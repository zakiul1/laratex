<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SliderImageController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home.index');
})->name('home');

Route::get('/dashboard', function () {
    return view('layouts.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//Public routes
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact.page');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');




// Admin routes with auth
Route::middleware(['auth'])->prefix('admin')->group(function () {

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
    //menu

    Route::resource('menus', MenuController::class);
    Route::post('/menus/{menu}/structure', [MenuController::class, 'updateStructure'])->name('menus.updateStructure');
    Route::post('/menus/select', function (Illuminate\Http\Request $request) {
        return redirect()->route('menus.edit', $request->menu_id);
    })->name('menus.select');

    // ✅ NEW: Add Page to Menu Route
    Route::post('/menus/{menu}/add-page', [MenuController::class, 'addPageToMenu'])->name('menus.addPage');

    //site settings

    Route::get('/site-settings', [SiteSettingController::class, 'edit'])->name('site-settings.edit');
    Route::match(['POST', 'PUT'], '/site-settings', [SiteSettingController::class, 'update'])
        ->name('site-settings.update');
    Route::delete('/site-settings/logo', [SiteSettingController::class, 'removeLogo'])->name('site-settings.remove-logo');

    //Contact Routes
    Route::get('/contact', [ContactController::class, 'adminForm'])->name('admin.contact.edit');
    Route::post('/contact', [ContactController::class, 'adminUpdate'])->name('admin.contact.update');

    //Route for Theme
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->name('index');
        Route::post('/{theme}/activate', [ThemeController::class, 'activate'])->name('activate');
        Route::get('/preview/{folder}', [ThemeController::class, 'preview'])->name('preview');
        Route::post('/duplicate/{folder}', [ThemeController::class, 'duplicate'])->name('duplicate');
        Route::get('/edit/{folder}', [ThemeController::class, 'edit'])->name('edit');
        Route::post('/upload', [ThemeController::class, 'upload'])->name('upload');
    });

});

require __DIR__ . '/auth.php';