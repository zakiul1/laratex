<?php
use App\Models\Category;

if (function_exists('add_filter')) {
    add_filter('the_content', function (string $content) {
        return preg_replace_callback(
            // match [featured_products] or [featured_products count=8] or [featured_products count="8"]
            '/\[featured_products(?:\s+count=(["\']?)(\d+)\1)?\]/',
            function (array $m) {
                // m[2] contains the number if given
                $count = isset($m[2]) ? (int) $m[2] : 12;

                // fetch “Featured Products” category
                $cat = Category::where('name', 'Featured Products')->first();
                if (!$cat) {
                    return '';
                }

                $products = $cat->products()->take($count)->get();

                // render your theme’s partial
                return view(
                    'themes.classic.partials.featured-products',
                    compact('products')
                )->render();
            },
            $content
        );
    }, 10);
}