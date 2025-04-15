<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $query = Slider::query()->with('images');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('layout', 'like', "%{$search}%");
        }

        $sliders = $query->orderBy('order')->paginate(10);

        return view('sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('sliders.create');
    }

    public function store(Request $request)
    {
        // ✅ Ensure checkboxes are submitted even if unchecked
        $request->merge([
            'show_arrows' => $request->input('show_arrows', 0),
            'show_indicators' => $request->input('show_indicators', 0),
            'status' => $request->input('status', 0),
        ]);

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
            'status' => 'boolean',
            'order' => 'nullable|integer',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slider = Slider::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sliders', 'public');
                $slider->images()->create([
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
        $request->merge([
            'show_arrows' => $request->input('show_arrows', 0),
            'show_indicators' => $request->input('show_indicators', 0),
            'status' => $request->input('status', 0),
        ]);

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
            'status' => 'boolean',
            'order' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slider->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sliders', 'public');
                $slider->images()->create([
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('sliders.index')->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        $slider->load('images');

        foreach ($slider->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $slider->delete();

        return response()->json(['success' => true]);
    }

    public function deleteImage($id)
    {
        $image = SliderImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true]);
    }
}