<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $data['slug'] = $slug;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('images');
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'status' => 'required|boolean',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $originalSlug = $data['slug'];
        $counter = 1;

        while (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        if ($request->hasFile('featured_image')) {
            if ($product->featured_image) {
                File::delete(public_path('storage/' . $product->featured_image));
            }
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        $product->update($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->featured_image) {
            File::delete(public_path('storage/' . $product->featured_image));
        }

        foreach ($product->images as $image) {
            File::delete(public_path('storage/' . $image->image));
            $image->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function deleteImage($id)
    {
        $image = ProductImage::findOrFail($id);
        File::delete(public_path('storage/' . $image->image));
        $image->delete();

        return response()->json(['success' => true]);
    }
    public function show(Product $product)
    {
        $categories = Category::all(); // for sidebar if needed
        $category = $product->category; // this is what’s missing

        return view('products.show', compact('product', 'categories', 'category'));
    }


}