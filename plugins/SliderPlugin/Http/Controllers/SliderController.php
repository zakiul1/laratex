<?php

namespace Plugins\SliderPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Plugins\SliderPlugin\Models\Slider;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->paginate(10);
        return view('slider-plugin::sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('slider-plugin::sliders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'layout' => 'required|string|in:one-column,two-column',
            'image_position' => 'required|in:left,right',
            'show_arrows' => 'nullable|boolean',
            'show_indicators' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        }

        $validated['show_arrows'] = $request->boolean('show_arrows');
        $validated['show_indicators'] = $request->boolean('show_indicators');

        Slider::create($validated);

        return redirect()->route('slider-plugin.sliders.index')->with('success', 'Slider created successfully.');
    }
}