<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Term;
use App\Models\TermTaxonomy;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /* ---------------------------------------------------------
     |  INDEX
     * --------------------------------------------------------*/
    public function index(Request $request)
    {
        $query = Product::with([
            'taxonomies.term',
            'featuredMedia'    // ← add this
        ]);

        if ($tid = $request->get('filter_category')) {
            $query->whereHas(
                'taxonomies',
                fn($q) =>
                $q->where('term_taxonomy_id', $tid)
            );
        }

        $products = $query->latest()->paginate(10);

        $allCats = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', 'terms.id')
            ->where('taxonomy', 'product')
            ->with('term')
            ->orderBy('terms.name')
            ->get();

        return view('products.index', compact('products', 'allCats'));
    }

    /* ---------------------------------------------------------
     |  CREATE
     * --------------------------------------------------------*/
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

    /* ---------------------------------------------------------
     |  STORE
     * --------------------------------------------------------*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',

            // <-- multiple featured images -->
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',

            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',

            // gallery (if you still need it)
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // collision-safe slug
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-" . ($i++);
        }
        $data['slug'] = $slug;

        // create product (we removed the single‐image logic)
        $product = Product::create($data);

        // sync featured images (many-to-many)
        $product->featuredMedia()->sync($request->input('featured_media_ids', []));

        // sync categories
        $product->taxonomies()->sync($request->input('taxonomy_ids', []));

        // gallery uploads (optional)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /* ---------------------------------------------------------
     |  EDIT
     * --------------------------------------------------------*/
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

    /* ---------------------------------------------------------
     |  UPDATE
     * --------------------------------------------------------*/
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "nullable|string|unique:products,slug,{$product->id}",
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',

            // <-- multiple featured images -->
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',

            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',

            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // collision-safe slug update
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (
            Product::where('slug', $slug)
                ->where('id', '!=', $product->id)
                ->exists()
        ) {
            $slug = "{$base}-" . ($i++);
        }
        $data['slug'] = $slug;

        // update
        $product->update($data);

        // re-sync featured images
        $product->featuredMedia()->sync($request->input('featured_media_ids', []));

        // re-sync categories
        $product->taxonomies()->sync($request->input('taxonomy_ids', []));

        // new gallery uploads (if you still need them)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /* ---------------------------------------------------------
     |  DESTROY
     * --------------------------------------------------------*/
    public function destroy(Product $product)
    {
        $product->taxonomies()->detach();

        foreach ($product->images as $img) {
            File::delete(public_path("storage/{$img->image}"));
            $img->delete();
        }
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }


    /* ---------------------------------------------------------
     |  FRONT-END SHOW
     * --------------------------------------------------------*/
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with('taxonomies.term', 'images', 'featuredMedia')
            ->firstOrFail();

        $firstCat = $product->taxonomies->first()?->term_taxonomy_id;
        $related = $firstCat
            ? Product::whereHas('taxonomies', fn($q) => $q->where('term_taxonomy_id', $firstCat))
                ->where('id', '!=', $product->id)
                ->where('status', 1)
                ->latest()
                ->take(4)
                ->get()
            : collect();

        $theme = getActiveTheme();
        $view = "themes.$theme.templates.product";

        return view()->exists($view)
            ? view($view, compact('product', 'related'))
            : view('products.show', compact('product', 'related'));
    }


    /**
     * AJAX endpoint to create a new “product” category on the fly.
     */

    public function ajaxCategoryStore(Request $request)
    {


        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => [
                'nullable',
                'integer',
                Rule::exists('term_taxonomies', 'term_taxonomy_id')
            ],
        ]);


        $slug = Str::slug($data['name']);

        if (Term::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 409);
        }

        $term = Term::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        $tax = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'product',
            'parent' => $data['parent'] ?? 0,
        ]);

        return response()->json([
            'id' => $tax->term_taxonomy_id,
            'name' => $term->name,
        ]);
    }

}