<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::with('images')->get();
        return view('sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('sliders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_url' => 'nullable|url',
            'layout' => 'required|in:one-column,two-column',
            'image_position' => 'required|in:left,right',
            'show_arrows' => 'boolean',
            'show_indicators' => 'boolean',
            'slider_location' => 'string',
            'images' => 'nullable|array', // ✅ Add this
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slider = Slider::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sliders', 'public');
                SliderImage::create([
                    'slider_id' => $slider->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('sliders.index')->with('success', 'Slider created successfully!');
    }

    public function edit(Slider $slider)
    {
        $slider->load('images');
        return view('sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_url' => 'nullable|url',
            'layout' => 'required|in:one-column,two-column',
            'image_position' => 'required|in:left,right',
            'show_arrows' => 'boolean',
            'show_indicators' => 'boolean',
            'slider_location' => 'string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slider->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sliders', 'public');
                SliderImage::create([
                    'slider_id' => $slider->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('sliders.index')->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('sliders.index')->with('success', 'Slider deleted successfully!');
    }

    public function deleteImage($id)
    {
        $image = \App\Models\SliderImage::findOrFail($id);
        \Storage::disk('public')->delete($image->image); // delete file too
        $image->delete();

        return response()->json(['success' => true]);
    }


}