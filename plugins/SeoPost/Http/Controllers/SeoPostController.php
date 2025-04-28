<?php

namespace Plugins\SeoPost\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SeoPostController extends Controller
{
    /**
     * Display the shortcode generator form.
     */
    public function configForm()
    {
        // Default values from config
        $defaults = config('seopost');

        // Available styles (blade files under resources/views/shortcode)
        $files = File::files(__DIR__ . '/../../resources/views/shortcode');
        $styles = collect($files)
            ->map(fn($file) => $file->getBasename('.blade.php'))
            ->toArray();

        return view('seopost-admin::config', compact('defaults', 'styles'));
    }

    /**
     * Validate input and build the [seopost] shortcode.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'cat'          => 'nullable|integer',
            'column'       => 'required|integer',
            'img'          => 'required|in:yes,no',
            'tcol'         => 'required|integer',
            'mcol'         => 'required|integer',
            'orderby'      => 'required|string',
            'get-price'    => 'required|in:yes,no',
            'order'        => 'required|in:ASC,DESC',
            'style'        => 'required|string',
            'taxo'         => 'nullable|string',
            'c-class'      => 'nullable|string',
            'post-id'      => 'nullable|integer',
            'excerpt-hide' => 'nullable|integer',
            'icon'         => 'required|in:yes,no',
            'bg'           => 'required|in:yes,no',
        ]);

        // Build attribute string
        $attrs = [];
        foreach ($validated as $key => $value) {
            if ($value !== null && $value !== '') {
                $attrs[] = $key . '="' . $value . '"';
            }
        }

        // Final shortcode
        $shortcode = '[seopost ' . implode(' ', $attrs) . ']';

        return redirect()
            ->route('seopost.config')
            ->with('shortcode', $shortcode);
    }
}
