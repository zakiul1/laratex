<?php

use App\Http\Controllers\Admin\HookController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PluginExportController;
use App\Http\Controllers\Admin\PluginImportController;
use App\Http\Controllers\Admin\PluginSettingController;
use App\Http\Controllers\Admin\PluginUpdateController;
use App\Http\Controllers\Admin\ProductTaxonomyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostTaxonomyController;        // ← Added
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeCustomizeController;
use App\Http\Controllers\WidgetController;
use App\Models\Product;
use App\Models\ThemeSetting;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

// Static front page (WordPress-style)
Route::get('/', [PageController::class, 'home'])
    ->name('home');

Route::get('storage/{path}', function ($path) {
    $full = storage_path('app/public/' . $path);
    if (!\Illuminate\Support\Facades\File::exists($full)) {
        abort(404);
    }
    return response()->file($full);
})->where('path', '.*');

Route::get('/aboroni', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public contact form
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// AJAX endpoint for runtime post-category creation
Route::post('posts/categories', [PostController::class, 'ajaxCategoryStore'])
    ->name('admin.posts.categories.store');

// Admin routes with auth
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // Post CRUD (you already have this)
    Route::resource('posts', PostController::class)->names('posts');

    // Post-category (taxonomy) CRUD
    Route::resource('post-taxonomies', PostTaxonomyController::class)
        ->except(['show']);

    // Pages
    Route::resource('pages', PageController::class)->except('show');

    // Products
    Route::resource('products', ProductController::class);
    Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy'])
        ->name('product-images.destroy');

    // Menus
    Route::resource('menus', MenuController::class);
    Route::post('/menus/{menu}/structure', [MenuController::class, 'updateStructure'])
        ->name('menus.updateStructure');
    Route::post('/menus/select', function (\Illuminate\Http\Request $request) {
        return redirect()->route('menus.edit', $request->menu_id);
    })->name('menus.select');
    Route::post('/menus/{menu}/add-page', [MenuController::class, 'addPageToMenu'])
        ->name('menus.addPage');
    Route::post('/menus/{menu}/add-category', [MenuController::class, 'addCategoryToMenu'])
        ->name('menus.add.category');
    Route::post('/menus/{menu}/add-post', [MenuController::class, 'addPostToMenu'])
        ->name('menus.add.post');

    // Site Settings
    Route::get('/site-settings', [SiteSettingController::class, 'edit'])
        ->name('site-settings.edit');
    Route::match(['POST', 'PUT'], '/site-settings', [SiteSettingController::class, 'update'])
        ->name('site-settings.update');
    Route::delete('/site-settings/logo', [SiteSettingController::class, 'removeLogo'])
        ->name('site-settings.remove-logo');

    // Contact (admin)
    Route::get('/contact', [ContactController::class, 'adminForm'])
        ->name('admin.contact.edit');
    Route::post('/contact', [ContactController::class, 'adminUpdate'])
        ->name('admin.contact.update');

    // Themes
    Route::post('/themes/{folder}/activate', [ThemeController::class, 'activate'])
        ->name('themes.activate');
    Route::delete('/themes/{folder}', [ThemeController::class, 'destroy'])
        ->name('themes.destroy');
    Route::post('/themes/upload', [ThemeController::class, 'upload'])
        ->name('themes.upload');
    Route::get('/themes', [ThemeController::class, 'index'])
        ->name('themes.index');
    Route::get('/themes/{folder}/preview', [ThemeController::class, 'preview'])
        ->name('themes.preview');

    // Theme Customization
    Route::get('themes/customize', [ThemeCustomizeController::class, 'edit'])
        ->name('themes.customize');
    Route::post('themes/customize', [ThemeCustomizeController::class, 'update'])
        ->name('themes.customize.update');
    Route::delete('themes/customize/reset', [ThemeCustomizeController::class, 'reset'])
        ->name('themes.customize.reset');
    Route::get('themes/customize/export', [ThemeCustomizeController::class, 'export'])
        ->name('themes.customize.export');
    Route::post('themes/customize/import', [ThemeCustomizeController::class, 'import'])
        ->name('themes.customize.import');

    // Widgets
    Route::resource('widgets', WidgetController::class);
    Route::post('/widgets/reorder', [WidgetController::class, 'reorder'])
        ->name('widgets.reorder');

    // Plugins
    Route::get('/plugins', [PluginController::class, 'index'])
        ->name('admin.plugins.index');
    Route::post('/plugins/{id}/toggle', [PluginController::class, 'toggle'])
        ->name('admin.plugins.toggle');
    Route::get('/hooks', [HookController::class, 'index'])
        ->name('admin.hooks.index');
    Route::get('/plugins/{slug}/settings', [PluginSettingController::class, 'edit'])
        ->name('admin.plugins.settings.edit');
    Route::post('/plugins/{slug}/settings', [PluginSettingController::class, 'update'])
        ->name('admin.plugins.settings.update');
    Route::get('/plugins/{slug}/export', [PluginExportController::class, 'export'])
        ->middleware('auth')
        ->name('admin.plugins.export');
    Route::get('/plugins/import', [PluginImportController::class, 'showForm'])
        ->name('admin.plugins.import.form');
    Route::post('/plugins/import', [PluginImportController::class, 'upload'])
        ->name('admin.plugins.import.upload');
    Route::delete('/plugins/{plugin}', [PluginController::class, 'destroy'])
        ->name('admin.plugins.destroy');
    Route::post('/plugins/{slug}/update', [PluginUpdateController::class, 'update'])
        ->middleware('auth')
        ->name('admin.plugins.update');

    // Products → Categories (AJAX)
    Route::post('/products/categories', [ProductController::class, 'ajaxCategoryStore'])
        ->name('admin.products.categories.store');

    // Product Taxonomies
    Route::resource('product-taxonomies', ProductTaxonomyController::class);

    // Categories (legacy)
    Route::resource('categories', CategoryController::class)
        ->names('categories');
});

// Media routes (outside the admin prefix but protected by auth)
Route::prefix('admin/media')->name('admin.media.')->group(function () {
    Route::get('/', [MediaController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('index');
    Route::delete('/bulk-delete', [MediaController::class, 'bulkDelete'])
        ->name('bulkDelete');
    Route::delete('/{media}', [MediaController::class, 'destroy'])
        ->name('destroy');
    Route::post('/upload', [MediaController::class, 'store'])
        ->name('store');
    Route::post('/categories', [MediaController::class, 'storeCategory'])
        ->name('categories.store');
    Route::get('/browser', [MediaController::class, 'browserIndex'])
        ->name('browserIndex');
});

require __DIR__ . '/auth.php';

// Public category & post pages
Route::get('/category/{slug}', [CategoryController::class, 'show'])
    ->name('categories.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])
    ->name('products.show');
Route::get('/pages/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('page.show');
Route::get('/posts/{slug}', [PostController::class, 'show'])
    ->name('posts.show');