<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Storage;

class ThemeCustomizeController extends Controller
{
    public function edit()
    {
        $theme = getActiveTheme();
        $settings = ThemeSetting::firstOrCreate(['theme' => $theme]);
        return view('admin.theme_customize', compact('settings'));
    }

    public function update(Request $request)
    {
        $theme = getActiveTheme();
        $settings = ThemeSetting::firstOrCreate(['theme' => $theme]);

        if ($request->hasFile('logo')) {
            $settings->logo = $request->file('logo')->store('theme_logos', 'public');
        }

        $settings->primary_color = $request->primary_color ?? '#0d6efd';
        $settings->custom_css = $request->custom_css;
        $settings->font_family = $request->font_family ?? 'sans-serif';
        $settings->footer_text = $request->footer_text;
        $settings->save();

        return redirect()->back()->with('success', 'Theme settings updated.');
    }

    public function reset()
    {
        ThemeSetting::where('theme', getActiveTheme())->delete();
        return back()->with('success', 'Theme settings reset to default.');
    }

    public function export()
    {
        $theme = getActiveTheme();
        $settings = ThemeSetting::where('theme', $theme)->first();

        if (!$settings) return back()->with('error', 'Nothing to export.');

        $json = json_encode($settings->toArray(), JSON_PRETTY_PRINT);
        $filename = $theme . '_settings.json';

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:json']);

        $data = json_decode(file_get_contents($request->file('import_file')), true);

        if (!isset($data['theme'])) {
            return back()->with('error', 'Invalid file format.');
        }

        ThemeSetting::updateOrCreate(
            ['theme' => $data['theme']],
            [
                'logo' => $data['logo'] ?? null,
                'primary_color' => $data['primary_color'] ?? null,
                'custom_css' => $data['custom_css'] ?? null,
                'font_family' => $data['font_family'] ?? null,
                'footer_text' => $data['footer_text'] ?? null,
            ]
        );

        return back()->with('success', 'Theme settings imported.');
    }
}