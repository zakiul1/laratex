<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductMeta; // in case seoMeta relation relies on this model
use App\Models\SiteSetting;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['taxonomies.term', 'featuredMedia']);

        if ($tid = $request->get('filter_category')) {
            $query->whereHas('taxonomies', fn($q) => $q->where('term_taxonomy_id', $tid));
        }

        $products = $query->latest()->paginate(10);

        $allCats = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', 'terms.id')
            ->where('taxonomy', 'product')
            ->with('term')
            ->orderBy('terms.name')
            ->get();

        $page = Post::where('type', 'page')
            ->where('slug', 'products')
            ->first();

        return view('products.index', compact('products', 'allCats', 'page'));
    }

    public function create()
    {
        $taxonomies = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'product')
            ->with('term')
            ->orderBy('terms.name')
            ->get();

        return view('products.create', compact('taxonomies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'price' => 'nullable|numeric',
            'style' => ['nullable', 'string', 'max:64', 'unique:products,style'],
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // SEO fields
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // Slug collision-proofing
        $base = $slug = $data['slug'] ?? Str::slug($data['name']);
        for ($i = 1; Product::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        $data['slug'] = $slug;

        // Create product (include style)
        $product = Product::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'style' => $data['style'] ?? null, // <-- added
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'] ?? null,
            'status' => $data['status'],
        ]);

        // Sync featured media
        $product->featuredMedia()->sync($data['featured_media_ids'] ?? []);

        // Sync categories (pivot stores object_type = product)
        $sync = [];
        foreach ($data['taxonomy_ids'] as $tid) {
            $sync[$tid] = ['object_type' => 'product'];
        }
        $product->taxonomies()->sync($sync);

        // Gallery uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        // Save SEO meta as a single json row under key "seo"
        $product->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($data['seo'] ?? [])]
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('taxonomies.term', 'images', 'featuredMedia');

        $taxonomies = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'product')
            ->with('term')
            ->orderBy('terms.name')
            ->get();

        return view('products.edit', compact('product', 'taxonomies'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "nullable|string|unique:products,slug,{$product->id}",
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'price' => 'nullable|numeric',
            // important: ignore current product id for unique style
            'style' => ['nullable', 'string', 'max:64', "unique:products,style,{$product->id}"],
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // SEO fields
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // Slug collision-proofing for updates
        $base = $slug = $data['slug'] ?? Str::slug($data['name']);
        for (
            $i = 1;
            Product::where('slug', $slug)->where('id', '!=', $product->id)->exists();
            $i++
        ) {
            $slug = "{$base}-{$i}";
        }
        $data['slug'] = $slug;

        // Update product (include style)
        $product->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'style' => $data['style'] ?? null, // <-- added
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'] ?? null,
            'status' => $data['status'],
        ]);

        // Re-sync featured media
        $product->featuredMedia()->sync($data['featured_media_ids'] ?? []);

        // Re-sync categories
        $sync = [];
        foreach ($data['taxonomy_ids'] as $tid) {
            $sync[$tid] = ['object_type' => 'product'];
        }
        $product->taxonomies()->sync($sync);

        // New gallery uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        // Update SEO meta
        $product->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($data['seo'] ?? [])]
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->taxonomies()->detach();

        foreach ($product->images as $img) {
            File::delete(public_path("storage/{$img->image}"));
            $img->delete();
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function ajaxCategoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => ['nullable', 'integer', 'exists:term_taxonomies,term_taxonomy_id'],
        ]);

        $slug = Str::slug($data['name']);

        if (Term::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 409);
        }

        $term = Term::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        $tt = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'product',
            'parent' => $data['parent'] ?? 0,
            'status' => 1,
            'description' => null,
            'count' => 0,
        ]);

        return response()->json([
            'id' => $tt->term_taxonomy_id,
            'name' => $term->name,
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::with(['featuredMedia', 'taxonomies.term'])
            ->where('slug', $slug)
            ->firstOrFail();

        $contentHtml = ElementFactory::json2html($product->content ?: '[]');

        $categoryTaxonomy = $product->taxonomies->first();

        $featuredCategory = TermTaxonomy::with(['term', 'products.featuredMedia'])
            ->where('taxonomy', 'product')
            ->whereHas('term', fn($q) => $q->where('name', 'Featured Products'))
            ->first();

        $featuredProducts = $featuredCategory ? $featuredCategory->products : collect();

        if (Schema::hasTable('site_settings')) {
            $settings = SiteSetting::firstOrCreate([]);
            $theme = $settings->active_theme ?: 'classic';
        } else {
            $theme = env('ACTIVE_THEME', 'classic');
        }

        $view = "themes.{$theme}.templates.product";
        if (!view()->exists($view)) {
            abort(404, "Template not found: {$view}");
        }

        return view($view, [
            'product' => $product,
            'category' => $categoryTaxonomy,
            'featuredCategory' => $featuredCategory,
            'featuredProducts' => $featuredProducts,
            'pageOutput' => $contentHtml,
        ]);
    }
}