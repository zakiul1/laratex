<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id')->all();

        foreach (range(1, 10) as $i) {
            $product = Product::create([
                'name' => "Product $i",
                'slug' => Str::slug("Product $i"),
                'description' => "This is a description for product $i.",
                'price' => rand(100, 1000),
                'category_id' => $categories[array_rand($categories)],
                'status' => rand(0, 1),
            ]);

            // Dummy image paths (ensure these images exist or update path)
            foreach (range(1, 3) as $imgIndex) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'products/sample' . $imgIndex . '.jpg', // You should place dummy images in `storage/app/public/products/`
                ]);
            }
        }
    }
}