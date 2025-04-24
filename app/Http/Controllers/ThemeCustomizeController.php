<?php

namespace App\Http\Controllers;

use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThemeCustomizeController extends Controller
{
    /**
     * Show the customization form.
     */
    public function edit()
    {
        $theme = getActiveTheme();

        // firstOrCreate with an empty options array
        $settings = ThemeSetting::firstOrCreate(
            ['theme' => $theme],
            [
                'logo' => null,
                'primary_color' => '#0d6efd',
                'font_family' => 'sans-serif',
                'footer_text' => '',
                'custom_css' => '',
                'options' => [],   // our JSON storage
            ]
        );

        return view('themes.customize', compact('settings'));
    }

    /**
     * Persist the form submission.
     */
    public function update(Request $request)
    {

        $theme = getActiveTheme();
        $settings = ThemeSetting::where('theme', $theme)->firstOrFail();

        // 1) Validate fixed columns + all dynamic keys
        $validated = $request->validate([
            // fixed fields
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'primary_color' => 'nullable|string|size:7',
            'font_family' => 'nullable|string|max:100',
            'footer_text' => 'nullable|string|max:255',
            'custom_css' => 'nullable|string',

            // Site Identity dynamic fields
            'site_title' => 'nullable|string|max:255',
            'site_title_color' => 'nullable|string|size:7',
            'tagline' => 'nullable|string|max:255',
            'tagline_color' => 'nullable|string|size:7',
            'show_tagline' => 'sometimes|boolean',
            'contact_phone' => 'nullable|string|max:50',

            // Typography subgroup (only examples shown; extend as needed)
            'typography.headings.h1' => 'nullable|numeric|min:0',
            'typography.headings.h2' => 'nullable|numeric|min:0',
            'typography.headings.h3' => 'nullable|numeric|min:0',
            'typography.headings.h4' => 'nullable|numeric|min:0',
            'typography.headings.h5' => 'nullable|numeric|min:0',
            'typography.headings.h6' => 'nullable|numeric|min:0',
            'typography.strong.weight' => 'nullable|numeric|min:100|max:900',
            'typography.paragraph.line_height' => 'nullable|string',
            'typography.list.marker' => 'nullable|string|in:disc,circle,square',
            'typography.anchor.color' => 'nullable|string|size:7',
        ]);

        // 2) Handle logo upload
        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $validated['logo'] = $request->file('logo')->store('theme', 'public');
        }

        // 3) Update fixed columns
        $settings->fill(Arr::only($validated, [
            'logo',
            'primary_color',
            'font_family',
            'footer_text',
            'custom_css',
        ]));

        // 4) Merge dynamic fields into options JSON
        $opts = $settings->options ?? [];

        // a) Site Identity fields
        foreach (['site_title', 'primary_color', 'site_title_color', 'tagline', 'tagline_color', 'contact_phone'] as $key) {
            $opts[$key] = $validated[$key] ?? $opts[$key] ?? null;
        }
        //dd($opts);
        // checkbox
        $opts['show_tagline'] = $request->boolean('show_tagline');

        // b) Full typography subtree
        if (isset($validated['typography']) && is_array($validated['typography'])) {
            $opts['typography'] = array_merge(
                $opts['typography'] ?? [],
                $validated['typography']
            );
        }

        $settings->options = $opts;

        $settings->save();

        // 5) Return JSON on AJAX, or redirect back normally
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme settings saved successfully.'
            ]);
        }

        return back()->with('success', 'Theme settings saved.');
    }

    /**
     * Reset to defaults.
     */
    public function reset()
    {
        $theme = getActiveTheme();
        $settings = ThemeSetting::where('theme', $theme)->firstOrFail();

        // delete uploaded logo file
        if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
            Storage::disk('public')->delete($settings->logo);
        }

        // reset fixed & clear options
        $settings->update([
            'logo' => null,
            'primary_color' => '#0d6efd',
            'font_family' => 'sans-serif',
            'footer_text' => '',
            'custom_css' => '',
            'options' => [],
        ]);

        return back()->with('success', 'Theme settings reset to defaults.');
    }

    /**
     * Export current settings as JSON.
     */
    public function export(): StreamedResponse
    {
        $theme = getActiveTheme();
        $settings = ThemeSetting::where('theme', $theme)->firstOrFail();

        $filename = "theme-settings-{$theme}.json";

        return new StreamedResponse(function () use ($settings) {
            echo json_encode($settings->toArray(), JSON_PRETTY_PRINT);
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import settings from JSON file.
     */
    public function import(Request $request)
    {
        $theme = getActiveTheme();
        $request->validate([
            'import_file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $json = file_get_contents($request->file('import_file')->getRealPath());
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return back()->withErrors('Import file is not valid JSON.');
        }

        $settings = ThemeSetting::firstOrCreate(['theme' => $theme]);

        // merge fixed columns
        $settings->fill(array_filter([
            'logo' => $data['logo'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'font_family' => $data['font_family'] ?? null,
            'footer_text' => $data['footer_text'] ?? null,
            'custom_css' => $data['custom_css'] ?? null,
        ]));

        // merge options subtree
        $settings->options = array_merge(
            $settings->options ?? [],
            $data['options'] ?? []
        );

        $settings->save();

        return back()->with('success', 'Theme settings imported.');
    }
}