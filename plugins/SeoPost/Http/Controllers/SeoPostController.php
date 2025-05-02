<?php

namespace Plugins\SeoPost\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SeoPostController extends Controller
{
    /**
     * Show the SEO Post shortcode generator / settings form.
     */
    public function configForm()
    {
        // 1) Load defaults from config/seopost.php
        $defaults = config('seopost');

        // 2) If the table exists, load any saved row
        $dbValues = [];
        if (Schema::hasTable('seopost_settings')) {
            $row = DB::table('seopost_settings')->first();
            if ($row) {
                $dbValues = (array) $row;
                // remove fields you don’t want editable
                unset($dbValues['id'], $dbValues['created_at'], $dbValues['updated_at']);
            }
        }

        // 3) Merge saved values on top of defaults
        $settings = array_merge($defaults, $dbValues);

        // 4) Gather available style templates
        $files = File::files(__DIR__ . '/../../resources/views/shortcode');
        $styles = collect($files)
            ->map(fn($file) => $file->getBasename('.blade.php'))
            ->toArray();

        // 5) Render the admin view, passing defaults, styles, and settings
        return view('seopost-admin::config', compact('defaults', 'styles', 'settings'));
    }

    /**
     * Handle form POST to save settings.
     */
    public function save(Request $request)
    {
        $data = $request->only(array_keys(config('seopost')));

        // Upsert into seopost_settings table
        DB::table('seopost_settings')->updateOrInsert(
            ['id' => 1],       // you could key off a single row
            $data + ['updated_at' => now()]
        );

        return redirect()->route('seopost.config')
            ->with('success', 'SeoPost settings saved.');
    }
}