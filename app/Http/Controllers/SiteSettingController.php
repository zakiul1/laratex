<?php

namespace App\Http\Controllers;

use App\Models\Post;               // ← use Post for pages
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function edit()
    {
        // ensure there’s always one settings row
        $setting = SiteSetting::firstOrCreate([]);

        // grab only posts that are pages
        $pages = Post::where('type', 'page')
            ->orderBy('title')
            ->get();

        return view('site-settings.edit', compact('setting', 'pages'));
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'site_name' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'show_ribbon' => 'nullable|in:0,1',
                'ribbon_left_text' => 'nullable|string|max:255',
                'ribbon_phone' => 'nullable|string|max:50',
                'ribbon_email' => 'nullable|email|max:255',
                'ribbon_bg_color' => 'nullable|string|max:20',
                'ribbon_text_color' => 'nullable|string|max:20',

                // now validate against posts table
                'home_page_slug' => 'nullable|exists:posts,slug',
            ]);

            $setting = SiteSetting::firstOrCreate([]);

            // logo upload/replace
            if ($request->hasFile('logo')) {
                if ($setting->logo) {
                    Storage::disk('public')->delete($setting->logo);
                }
                $validated['logo'] = $request->file('logo')
                    ->store('logos', 'public');
            }

            $validated['show_ribbon'] = $request->boolean('show_ribbon');

            $setting->update($validated);

            return response()->json([
                'message' => 'Site settings updated successfully.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Site settings update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeLogo()
    {
        $setting = SiteSetting::firstOrFail();

        if ($setting->logo) {
            Storage::disk('public')->delete($setting->logo);
            $setting->update(['logo' => null]);
        }

        return back()->with('success', 'Logo removed.');
    }
}