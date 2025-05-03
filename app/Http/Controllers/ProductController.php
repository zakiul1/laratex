<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['taxonomies.term', 'featuredMedia']);

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
        //dd($request->all());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Slug collision-proofing
        $base = $slug = $data['slug'] ?? Str::slug($data['name']);
        for ($i = 1; Product::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        $data['slug'] = $slug;

        $product = Product::create($data);

        // Sync featured images
        $product->featuredMedia()->sync($data['featured_media_ids'] ?? []);

        // Sync categories
        $product->taxonomies()->sync($data['taxonomy_ids']);

        // Optional gallery uploads
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
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Slug collision-proofing
        $base = $slug = $data['slug'] ?? Str::slug($data['name']);
        for ($i = 1; Product::where('slug', $slug)->where('id', '!=', $product->id)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        $data['slug'] = $slug;

        $product->update($data);

        // Re-sync featured images
        $product->featuredMedia()->sync($data['featured_media_ids'] ?? []);

        // Re-sync categories
        $product->taxonomies()->sync($data['taxonomy_ids']);

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

        return redirect()->route('products.index')
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

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }


    /**
     * AJAX endpoint: create a product category on the fly.
     */
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

        // create term
        $term = Term::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        // create taxonomy entry
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

}