<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    // app/Http/Controllers/CategoryController.php

    public function index()
    {
        // 1) Paginate server-side
        $paginated = Category::orderBy('created_at', 'desc')
            ->paginate(10);

        // 2) Pass the current page’s items as plain array for Alpine
        $categories = $paginated->items();

        return view('categories.index', [
            'categories' => $categories,
            'paginated' => $paginated,
        ]);
    }


    public function create()
    {
        $allCategories = Category::all();
        // provide an empty Category for the SEO form
        $category = new Category();

        return view('categories.create', compact('allCategories', 'category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean',
            'parent_id' => 'nullable|exists:categories,id',

            // ─── SEO fields ────────────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // slug generation
        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }
        $data['slug'] = $slug;

        // featured image
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('categories', 'public');
        }

        // create the category
        /** @var Category $category */
        $category = Category::create($data);

        // sync SEO JSON
        $category->seoMeta()
            ->updateOrCreate([], ['meta' => $data['seo']]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
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

            // ─── SEO fields ────────────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // slug regeneration if needed
        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;

        while (
            Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }
        $data['slug'] = $slug;

        // handle new image upload
        if ($request->hasFile('featured_image')) {
            if ($category->featured_image) {
                File::delete(public_path("storage/{$category->featured_image}"));
            }
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('categories', 'public');
        }

        // update the category
        $category->update($data);

        // update SEO JSON
        $category->seoMeta()
            ->updateOrCreate([], ['meta' => $data['seo']]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->featured_image) {
            File::delete(public_path("storage/{$category->featured_image}"));
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with('products')
            ->firstOrFail();

        $products = $category->products;
        $allCategories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        $theme = getActiveTheme();
        $themeView = "themes.{$theme}.templates.category";

        if (view()->exists($themeView)) {
            return view($themeView, compact(
                'category',
                'products',
                'allCategories'
            ));
        }

        // fallback
        abort(404);
    }
}