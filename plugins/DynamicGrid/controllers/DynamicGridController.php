<?php

namespace Plugins\DynamicGrid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Term;
use App\Models\TermTaxonomy;

class DynamicGridController extends Controller
{
    /**
     * Show the shortcode builder form in the admin.
     */
    public function builderForm()
    {
        $config = config('dynamicgrid');
        $layouts = $config['layouts'];
        $taxonomies = TermTaxonomy::distinct()->pluck('taxonomy')->toArray();

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

    /**
     * Return JSON categories for a given taxonomy.
     */
    public function getCategories($taxonomy)
    {
        $cats = Term::whereHas('taxonomies', fn($q) => $q->where('taxonomy', $taxonomy))
            ->get(['id', 'name']);

        return response()->json($cats);
    }

    /**
     * Generate the [dynamicgrid] shortcode.
     */
    public function generateShortcode(Request $req)
    {
        $defaults = config('dynamicgrid');

        $validated = $req->validate([
            'taxonomy' => 'required|string',
            'category_id' => 'nullable|integer',
            'type' => 'required|in:single_post,feature_post,widget_post',
            'layout' => 'required|string',
            'columns.*' => 'sometimes|integer|min:1',
            'excerpt_words' => 'sometimes|integer|min:0',
            'show_image' => 'sometimes|boolean',
            'show_description' => 'sometimes|boolean',
            'button_type' => 'required|in:none,read_more,price',
            'heading' => 'nullable|string',
            'post_id' => 'nullable|integer',
            'product_amount' => 'sometimes|integer|min:1',
        ]);

        $data = array_merge($defaults, $validated);

        $data['show_image'] = $req->has('show_image') ? '1' : '0';
        $data['show_description'] = $req->has('show_description') ? '1' : '0';

        $attrs = [
            'taxonomy' => $data['taxonomy'],
            'category_id' => $data['category_id'],
            'type' => $data['type'],
            'layout' => $data['layout'],
            'product_amount' => $data['product_amount'],
            'button_type' => $data['button_type'],
        ];

        if ($data['show_description'] === '1') {
            $attrs['show_description'] = '1';
        }
        if (!empty(trim($data['heading']))) {
            $attrs['heading'] = trim($data['heading']);
        }
        if (!empty($data['excerpt_words'])) {
            $attrs['excerpt_words'] = intval($data['excerpt_words']);
        }
        if ($data['show_image'] === '1') {
            $attrs['show_image'] = '1';
        }
        if ($data['type'] === 'feature_post' && is_array($data['columns'])) {
            foreach ($data['columns'] as $device => $count) {
                $attrs["columns_{$device}"] = intval($count);
            }
        }

        $shortcode = '[dynamicgrid';
        foreach ($attrs as $key => $val) {
            $shortcode .= " {$key}=\"" . e($val) . '"';
        }
        $shortcode .= ']';

        return back()->with('shortcode', $shortcode);
    }

    /**
     * Handle the frontend “Request Price” AJAX form submit.
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

        // Build HTML list of product links
        $htmlList = '<ul>';
        foreach ($data['products'] as $prod) {
            $title = e($prod['title']);
            $url = e($prod['url']);
            $htmlList .= "<li><a href=\"{$url}\" target=\"_blank\">{$title}</a></li>";
        }
        $htmlList .= '</ul>';

        // Compose the full message
        $fullMessage = nl2br(e($data['message']))
            . "<br/><br/>Selected Products:<br/>{$htmlList}";
        // dd($fullMessage);

        // Send to external API using Laravel HTTP client with extended timeout & retries
        // dd($fullMessage);
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => 'KE3718bbc060626178b19fce51a6ea7Y',
            ])
                ->timeout(30)
                ->connectTimeout(10)
                ->retry(3, 100)
                ->post('http://edesk.test/api/send', [
                    'ip' => '192.27.27.1',
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}