<?php

namespace Plugins\DynamicGrid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Term;
use App\Models\TermTaxonomy;

class DynamicGridController extends Controller
{
    public function builderForm()
    {
        $config = config('dynamicgrid');
        $layouts = $config['layouts'];
        $taxonomies = TermTaxonomy::distinct()->pluck('taxonomy')->toArray();

        // load categories for the first taxonomy (if any)
        $firstTax = $taxonomies[0] ?? null;
        $categories = $firstTax
            ? Term::whereHas('taxonomies', fn($q) => $q->where('taxonomy', $firstTax))
                ->get(['id', 'name'])
            : collect();

        return view('dynamicgrid::admin.builder', compact(
            'config',
            'layouts',
            'taxonomies',
            'categories'
        ));
    }

    public function getCategories($taxonomy)
    {
        $cats = Term::whereHas(
            'taxonomies',
            fn($q) =>
            $q->where('taxonomy', $taxonomy)
        )->get(['id', 'name']);
        return response()->json($cats);
    }

    public function generateShortcode(Request $req)
    {
        $data = $req->validate([
            'taxonomy' => 'required|string',
            'category_id' => 'nullable|integer',
            'type' => 'required|in:single_post,feature_post,widget_post',
            'layout' => 'required|string',
            'columns.*' => 'required|integer|min:1',
            'excerpt_words' => 'required|integer|min:0',
            'show_image' => 'boolean',
            'button_type' => 'nullable|in:none,read_more,price',
            'heading' => 'nullable|string',
            'post_id' => 'nullable|integer',
        ]);

        // build attribute string
        $attrs = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $sub => $val) {
                    $attrs[] = "{$k}_{$sub}=\"{$val}\"";
                }
            } elseif ($v !== null && $v !== '') {
                $attrs[] = "{$k}=\"{$v}\"";
            }
        }

        $shortcode = '[dynamicgrid ' . implode(' ', $attrs) . ']';
        return back()->with('shortcode', $shortcode);
    }
}