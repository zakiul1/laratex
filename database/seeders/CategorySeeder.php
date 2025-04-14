<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{

    public function run(): void
    {
        DB::table('categories')->truncate(); // Clears all categories

        $categories = ['Men', 'Women', 'Kids', 'Accessories'];

        foreach ($categories as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => true,
            ]);
        }
    }
}