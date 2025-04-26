<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    /**
     * Display a paginated list of categories (term_taxonomies where taxonomy = 'category').
     */
    public function index()
    {
        $paginated = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = $paginated->items();

        return view('categories.index', compact('categories', 'paginated'));
    }

    /**
     * Show form to create a new category.
     */
    public function create()
    {
        $allCategories = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->get();
        $category = new TermTaxonomy();

        return view('categories.create', compact('allCategories', 'category'));
    }

    /**
     * Store a new category (as a term + taxonomy).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:terms,slug',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parent' => 'nullable|exists:term_taxonomies,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // default SEO array so we never index null
        $seoData = $data['seo'] ?? [];

        // generate unique slug
        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Term::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        $term = Term::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('categories', 'public');
        }

        $category = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'category',
            'description' => $data['description'] ?? null,
            'parent' => $data['parent'] ?? 0,
            'status' => $data['status'],
            'featured_image' => $data['featured_image'] ?? null,
        ]);

        // always safe now, even if SEO was omitted
        $category->seoMeta()->updateOrCreate([], ['meta' => $seoData]);

        // JSON response for AJAX
        if ($request->wantsJson()) {
            $category->load('term');
            return response()->json([
                'id' => $category->id,
                'term' => ['name' => $category->term->name, 'slug' => $category->term->slug],
                'parent' => $category->parent,
            ]);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }


    /**
     * Show form to edit an existing category.
     */
    public function edit($id)
    {
        $category = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->findOrFail($id);

        $allCategories = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->where('id', '!=', $id)
            ->get();

        return view('categories.edit', compact('category', 'allCategories'));
    }

    /**
     * Update an existing category term + taxonomy.
     */
    public function update(Request $request, $id)
    {
        $category = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:terms,slug,' . $category->term->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parent' => 'nullable|exists:term_taxonomies,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // Regenerate slug if name or slug changed
        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (
            Term::where('slug', $slug)
                ->where('id', '!=', $category->term->id)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        // Update term
        $category->term->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        // Handle featured image replacement
        if ($request->hasFile('featured_image')) {
            if ($category->featured_image) {
                File::delete(public_path("storage/{$category->featured_image}"));
            }
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('categories', 'public');
        }

        // Update taxonomy
        $category->update([
            'description' => $data['description'] ?? null,
            'parent' => $data['parent'] ?? 0,
            'status' => $data['status'],
            'featured_image' => $data['featured_image'] ?? $category->featured_image,
        ]);

        // Sync SEO meta
        $category->seoMeta()->updateOrCreate([], ['meta' => $data['seo']]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category term + taxonomy.
     */
    public function destroy($id)
    {
        $category = TermTaxonomy::where('taxonomy', 'category')->findOrFail($id);

        if ($category->featured_image) {
            File::delete(public_path("storage/{$category->featured_image}"));
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Display the front-end category template.
     */
    public function show($slug)
    {
        $category = TermTaxonomy::with(['term', 'posts'])
            ->where('taxonomy', 'category')
            ->whereHas('term', fn($q) => $q->where('slug', $slug))
            ->firstOrFail();

        $products = $category->posts;
        $allCategories = TermTaxonomy::with('term', 'children')
            ->where('taxonomy', 'category')
            ->where('parent', 0)
            ->get();

        $theme = getActiveTheme();
        $themeView = "themes.{$theme}.templates.category";

        if (view()->exists($themeView)) {
            return view($themeView, compact('category', 'products', 'allCategories'));
        }

        abort(404);
    }
}