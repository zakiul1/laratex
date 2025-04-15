<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $setting = SiteSetting::firstOrCreate([]);
        return view('site-settings.edit', compact('setting'));
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
            ]);

            // Ensure model exists
            $setting = SiteSetting::firstOrCreate([]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                if ($setting->logo) {
                    Storage::disk('public')->delete($setting->logo);
                }
                $validated['logo'] = $request->file('logo')->store('logos', 'public');
            }

            // Force boolean for checkbox input
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