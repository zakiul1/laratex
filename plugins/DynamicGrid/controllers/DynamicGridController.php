<?php

namespace Plugins\DynamicGrid\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class DynamicGridController extends Controller
{
    /**
     * Show the admin “Dynamic Grid” shortcode builder form.
     */
    public function builderForm()
    {
        // Load our plugin’s config
        $config = config('dynamicgrid');
        $layouts = $config['layouts'];

        // Fetch all distinct taxonomy slugs
        $taxonomies = TermTaxonomy::distinct('taxonomy')
            ->pluck('taxonomy')
            ->toArray();

        // Seed the first taxonomy’s terms for the initial “Category” select
        $firstTax = $taxonomies[0] ?? null;
        $categories = $firstTax
            ? Term::whereHas('taxonomies', fn($q) => $q->where('taxonomy', $firstTax))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('dynamicgrid::admin.builder', compact(
            'config',
            'layouts',
            'taxonomies',
            'categories'
        ));
    }

    /**
     * AJAX: return JSON list of terms for the given taxonomy slug.
     */
    public function getCategories(string $taxonomy)
    {
        $cats = Term::whereHas('taxonomies', fn($q) => $q->where('taxonomy', $taxonomy))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cats);
    }

    /**
     * Validate input & assemble the [dynamicgrid …] shortcode.
     * Stores the result in session('shortcode') and redirects back.
     */
    public function generateShortcode(Request $req)
    {
        $defaults = config('dynamicgrid');

        $validated = $req->validate([
            'taxonomy' => ['required', 'string'],
            'category_id' => ['nullable', 'integer'],
            'type' => ['required', Rule::in(array_keys($defaults['layouts']))],
            'layout' => ['required', 'string'],
            'columns.*' => ['sometimes', 'integer', 'min:1'],
            'excerpt_words' => ['sometimes', 'integer', 'min:0'],
            'show_image' => ['sometimes', 'boolean'],
            'show_description' => ['sometimes', 'boolean'],
            'button_type' => ['required', Rule::in(['none', 'read_more', 'price'])],
            'heading' => ['nullable', 'string'],
            'post_id' => ['nullable', 'integer'],
            'product_amount' => ['sometimes', 'integer', 'min:1'],
        ]);

        // Merge with plugin defaults
        $data = array_merge($defaults, $validated);

        // Build the initial shortcode attributes
        $attrs = [
            'taxonomy' => $data['taxonomy'],
            'category_id' => $data['category_id'] ?? '',
            'type' => $data['type'],
            'layout' => $data['layout'],
            'button_type' => $data['button_type'],
            'product_amount' => $data['product_amount'] ?? '',
        ];

        if ($req->has('show_description')) {
            $attrs['show_description'] = '1';
        }
        if ($req->has('show_image')) {
            $attrs['show_image'] = '1';
        }
        if (!empty($data['excerpt_words'])) {
            $attrs['excerpt_words'] = intval($data['excerpt_words']);
        }
        if (!empty(trim($data['heading']))) {
            $attrs['heading'] = trim($data['heading']);
        }
        if ($data['type'] === 'feature_post' && is_array($data['columns'])) {
            foreach ($data['columns'] as $device => $count) {
                $attrs["columns_{$device}"] = intval($count);
            }
        }

        // For feature_post + layout1, remove unwanted attributes
        if ($data['type'] === 'feature_post' && $data['layout'] === 'layout1') {
            foreach (['button_type', 'show_image', 'excerpt_words', 'columns_mobile', 'columns_tablet', 'columns_medium', 'columns_desktop', 'columns_large',] as $key) {
                unset($attrs[$key]);
            }
        }

        // Assemble the final shortcode string
        $shortcode = '[dynamicgrid';
        foreach ($attrs as $key => $val) {
            $shortcode .= " {$key}=\"" . e($val) . '"';
        }
        $shortcode .= ']';

        return back()->with('shortcode', $shortcode);
    }

    /**
     * Front-end handler for the “Request Price” AJAX form.
     */
    public function requestPrice(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:99',
            'email' => 'required|email|max:256',
            'whatsapp' => 'nullable|string|max:30',
            'message' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|integer',
            'products.*.title' => 'required|string',
            'products.*.url' => 'required|url',
            'products.*.img' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Build HTML list of selected products
        $htmlList = '<ul>';
        foreach ($data['products'] as $prod) {
            $title = e($prod['title']);
            $url = e($prod['url']);
            $htmlList .= "<li><a href=\"{$url}\" target=\"_blank\">{$title}</a></li>";
        }
        $htmlList .= '</ul>';

        $fullMessage = nl2br(e($data['message'])) . "<br/><br/>Selected Products:<br/>{$htmlList}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => config('dynamicgrid.request_price_api_key'),
            ])
                ->timeout(30)
                ->connectTimeout(10)
                ->retry(3, 100)
                ->post(config('dynamicgrid.request_price_endpoint'), [
                    'ip' => $req->ip(),
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'whatsapp' => $data['whatsapp'] ?? '',
                    'subject' => 'Price Request from ' . $data['name'],
                    'message' => $fullMessage,
                ]);

            $body = $response->json();

            return response()->json([
                'success' => true,
                'message' => $body['message'] ?? 'Request sent successfully!',
                'api_response' => $body,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}