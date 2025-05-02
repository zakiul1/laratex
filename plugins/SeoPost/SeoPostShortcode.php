<?php

namespace Plugins\SeoPost;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Arr;
use App\Models\Product;

class SeoPostShortcode
{
    /**
     * Render the [seopost] shortcode against products.
     *
     * @param  array       $atts    Shortcode attributes (cat, column, style…)
     * @param  null|string $content Not used
     * @return string               Rendered HTML
     */
    public static function handle(array $atts, ?string $content = null): string
    {
        // 1) Merge plugin defaults with passed attributes
        $defaults = config('seopost');
        $attrs = array_merge($defaults, $atts);

        // 2) Build the Product query and eager-load featured media
        $query = Product::query()
            ->with('featuredMedia')
            ->when(
                !empty($attrs['cat']),
                fn($q) =>
                $q->whereHas(
                    'taxonomies',
                    fn($q2) =>
                    $q2->where('term_relationships.term_taxonomy_id', $attrs['cat'])
                )
            )
            ->when(
                !empty($attrs['post-id']),
                fn($q) =>
                $q->where('id', $attrs['post-id'])
            )
            ->orderBy(
                $attrs['orderby'] ?? 'id',
                strtoupper($attrs['order'] ?? 'ASC')
            );

        $products = $query->get();

        // 3) Determine which Blade view/style to render
        $style = $attrs['style'] ?? $defaults['style'];
        $view = "seopost::{$style}";

        // 4) Extract wrapper CSS class
        $wrapperClass = Arr::get($attrs, 'c-class', '');

        // 5) Render view, passing all necessary variables
        return View::make($view, [
            'products' => $products,
            'posts' => $products,   // for backward compatibility
            'attrs' => $attrs,
            'settings' => $attrs,
            'wrapperClass' => $wrapperClass,
        ])->render();
    }
}