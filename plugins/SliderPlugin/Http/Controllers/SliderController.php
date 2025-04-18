<?php

namespace Plugins\SliderPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Plugins\SliderPlugin\Models\Slider;
use Plugins\SliderPlugin\Models\SliderImage;   // ⇦ make sure this model exists

class SliderController extends Controller
{
    /* --------------------------------------------------------------------------
     |  LIST
     * --------------------------------------------------------------------------*/
    public function index(Request $request)
    {
        $query = Slider::query();

        // optional filters (match the index view)
        if ($search = $request->input('q')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($layout = $request->input('layout')) {
            $query->where('layout', $layout);
        }

        $sliders = $query->latest()->paginate(10);

        return view('slider-plugin::sliders.index', compact('sliders'));
    }

    /* --------------------------------------------------------------------------
     |  CREATE
     * --------------------------------------------------------------------------*/
    public function create()
    {
        return view('slider-plugin::sliders.create');
    }

    /* --------------------------------------------------------------------------
     |  STORE  (handles **multiple** images)
     * --------------------------------------------------------------------------*/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'nullable|string|max:255',
            'subtitle'         => 'nullable|string|max:255',
            'content'          => 'nullable|string',
            'button_text'      => 'nullable|string|max:255',
            'button_url'       => 'nullable|string|max:255',
            'layout'           => 'required|string|in:one-column,two-column',
            'image_position'   => 'required|in:left,right',
            'show_arrows'      => 'nullable|boolean',
            'show_indicators'  => 'nullable|boolean',
            'images.*'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $slider = Slider::create([
            'title'           => $validated['title']           ?? null,
            'subtitle'        => $validated['subtitle']        ?? null,
            'content'         => $validated['content']         ?? null,
            'button_text'     => $validated['button_text']     ?? null,
            'button_url'      => $validated['button_url']      ?? null,
            'layout'          => $validated['layout'],
            'image_position'  => $validated['image_position'],
            'show_arrows'     => $request->boolean('show_arrows'),
            'show_indicators' => $request->boolean('show_indicators'),
        ]);

        /* ---------- store uploaded images & link ---------- */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('sliders', 'public');
                $slider->images()->create(['file_path' => $path]);

                // keep first image as thumbnail (optional)
                if (!$slider->image) {
                    $slider->update(['image' => $path]);
                }
            }
        }

        return redirect()
            ->route('slider-plugin.sliders.index')
            ->with('success', 'Slider created successfully.');
    }

    /* --------------------------------------------------------------------------
     |  EDIT
     * --------------------------------------------------------------------------*/
    public function edit(Slider $slider)
    {
        $slider->load('images');
        return view('slider-plugin::sliders.edit', compact('slider'));
    }

    /* --------------------------------------------------------------------------
     |  UPDATE  (adds new images, keeps old)
     * --------------------------------------------------------------------------*/
    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title'            => 'nullable|string|max:255',
            'subtitle'         => 'nullable|string|max:255',
            'content'          => 'nullable|string',
            'button_text'      => 'nullable|string|max:255',
            'button_url'       => 'nullable|string|max:255',
            'layout'           => 'required|string|in:one-column,two-column',
            'image_position'   => 'required|in:left,right',
            'show_arrows'      => 'nullable|boolean',
            'show_indicators'  => 'nullable|boolean',
            'images.*'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $slider->update([
            'title'           => $validated['title']           ?? null,
            'subtitle'        => $validated['subtitle']        ?? null,
            'content'         => $validated['content']         ?? null,
            'button_text'     => $validated['button_text']     ?? null,
            'button_url'      => $validated['button_url']      ?? null,
            'layout'          => $validated['layout'],
            'image_position'  => $validated['image_position'],
            'show_arrows'     => $request->boolean('show_arrows'),
            'show_indicators' => $request->boolean('show_indicators'),
        ]);

        /* ---------- append newly‑uploaded images ---------- */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('sliders', 'public');
                $slider->images()->create(['file_path' => $path]);

                if (!$slider->image) {
                    $slider->update(['image' => $path]);
                }
            }
        }

        return redirect()
            ->route('slider-plugin.sliders.index')
            ->with('success', 'Slider updated successfully.');
    }

    /* --------------------------------------------------------------------------
     |  DELETE
     * --------------------------------------------------------------------------*/
    public function destroy(Slider $slider)
    {
        // delete physical files
        foreach ($slider->images as $img) {
            Storage::disk('public')->delete($img->file_path);
        }
        Storage::disk('public')->delete($slider->image);

        $slider->delete();

        return redirect()
            ->route('slider-plugin.sliders.index')
            ->with('success', 'Slider deleted.');
    }
}
