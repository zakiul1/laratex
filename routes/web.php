<?php

use App\Http\Controllers\Admin\HookController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PluginExportController;
use App\Http\Controllers\Admin\PluginImportController;
use App\Http\Controllers\Admin\PluginSettingController;
use App\Http\Controllers\Admin\PluginUpdateController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
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
use App\Http\Controllers\ThemeCustomizeController;
use App\Http\Controllers\WidgetController;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $theme = ThemeSetting::first()?->theme ?? 'default';

    $viewPath = "themes.$theme.home";

    if (view()->exists($viewPath)) {
        return view($viewPath);
    }

    abort(404, "Theme view '$viewPath' not found.");
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

//Route::get('/contact', [ContactController::class, 'index'])->name('contact.page');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');





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
    Route::resource('pages', PageController::class)->except('show');

    //Category
    Route::resource('categories', CategoryController::class);

    //Products
    Route::resource('products', ProductController::class);
    Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
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

    Route::post('/themes/{folder}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::delete('/themes/{folder}', [ThemeController::class, 'destroy'])->name('themes.destroy');
    Route::post('/themes/upload', [ThemeController::class, 'upload'])->name('themes.upload');
    Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::get('/themes/{folder}/preview', [ThemeController::class, 'preview'])->name('themes.preview');

    // Theme Customization
    Route::get('/themes/customize', [ThemeCustomizeController::class, 'edit'])->name('themes.customize');
    Route::post('/themes/customize', [ThemeCustomizeController::class, 'update'])->name('themes.customize.update');
    Route::delete('/themes/customize/reset', [ThemeCustomizeController::class, 'reset'])->name('themes.customize.reset');
    Route::get('/themes/customize/export', [ThemeCustomizeController::class, 'export'])->name('themes.customize.export');
    Route::post('/themes/customize/import', [ThemeCustomizeController::class, 'import'])->name('themes.customize.import'); // ✅ This is the missing one

    // Widgets Routes

    Route::resource('widgets', WidgetController::class);
    Route::post('/widgets/reorder', [WidgetController::class, 'reorder'])->name('widgets.reorder');

    //Plugin Routes
    Route::get('/plugins', [PluginController::class, 'index'])->name('admin.plugins.index');
    Route::post('/plugins/{id}/toggle', [PluginController::class, 'toggle'])->name('admin.plugins.toggle');

    Route::get('/hooks', [HookController::class, 'index'])->name('admin.hooks.index');

    Route::get('/plugins/{slug}/settings', [PluginSettingController::class, 'edit'])->name('admin.plugins.settings.edit');
    Route::post('/plugins/{slug}/settings', [PluginSettingController::class, 'update'])->name('admin.plugins.settings.update');
    Route::get('/plugins/{slug}/export', [PluginExportController::class, 'export'])
        ->middleware('auth')
        ->name('admin.plugins.export');


    // Media Library
    // web.php
    Route::resource('media', MediaController::class);



    //Plugin Import Routes
    Route::get('/plugins/import', [PluginImportController::class, 'showForm'])->name('admin.plugins.import.form');
    Route::post('/plugins/import', [PluginImportController::class, 'upload'])->name('admin.plugins.import.upload');
    Route::delete('/plugins/{plugin}', [PluginController::class, 'destroy'])->name('admin.plugins.destroy');


    //Plugin Update Routes

    Route::post('/plugins/{slug}/update', [PluginUpdateController::class, 'update'])
        ->middleware('auth')
        ->name('admin.plugins.update');

});



require __DIR__ . '/auth.php';


Route::get('/posts/{slug}', [PostController::class, 'show'])
    ->name('posts.show');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');