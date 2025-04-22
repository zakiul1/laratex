<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $allCategories = Category::all();
        return view('categories.create', compact('allCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $data['slug'] = $slug;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $allCategories = Category::where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'allCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $data['slug'] = $slug;

        if ($request->hasFile('featured_image')) {
            if ($category->featured_image) {
                File::delete(public_path('storage/' . $category->featured_image));
            }
            $data['featured_image'] = $request->file('featured_image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->featured_image) {
            File::delete(public_path('storage/' . $category->featured_image));
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function show($slug)
    {
        // 1) Load the category and its products
        $category = Category::where('slug', $slug)
            ->with('products')   // make sure Category has a products() relation
            ->firstOrFail();

        $products = $category->products;
        $allCategories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        // 2) Determine the active theme (use whatever helper your app provides)
        $theme = getActiveTheme();
        // e.g. returns "default" or "myTheme"

        // 3) Construct the theme template name
        $themeView = "themes.{$theme}.templates.category";

        // 4) If the theme provides its own category.blade.php, use it...
        if (view()->exists($themeView)) {
            return view($themeView, compact('category', 'products', 'allCategories'));
        }

        /*   // 5) Otherwise fall back to our plugin/module view
          return view('categories.view', compact('category', 'products', 'allCategories')); */
    }

}