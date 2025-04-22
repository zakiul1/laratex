<?php

namespace Plugins\SliderPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Plugins\SliderPlugin\Models\Slider;
use Plugins\SliderPlugin\Models\SliderItem;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::all();
        return view('slider-plugin::sliders.index', compact('sliders'));
    }

    public function create()
    {
        $slider = new Slider;
        $items = old('items', []);
        return view('slider-plugin::sliders.form', compact('slider', 'items'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSlider($request);
        $slider = Slider::create($data);

        // run saveItems once to add all slides
        $this->saveItems($request, $slider);

        return redirect()
            ->route('slider-plugin.sliders.index')
            ->with('success', 'Slider Plugin created successfully.');
    }

    public function edit($id)
    {
        $slider = Slider::with('items')->findOrFail($id);

        // reuse old input on validation error, otherwise map real items
        $items = old('items')
            ? old('items')
            : $slider->items->map(function (SliderItem $it) {
                return [
                    'id' => $it->id,
                    'existing_image_path' => $it->image_path,
                    'content' => $it->content,
                ];
            })->toArray();

        return view('slider-plugin::sliders.form', compact('slider', 'items'));
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);
        $data = $this->validateSlider($request, $slider->id);

        $slider->update($data);

        // ⚠️ Delete ALL old slides and re-add from the form
        $slider->items()->delete();
        $this->saveItems($request, $slider);

        return back()->with('success', 'Slider Plugin updated successfully.');
    }

    public function destroy($id)
    {
        Slider::findOrFail($id)->delete();
        return back()->with('success', 'Slider Plugin removed.');
    }

    public function destroyItemImage($sliderId, $itemId)
    {
        $item = SliderItem::findOrFail($itemId);
        abort_if($item->slider_id != $sliderId, 404);

        Storage::disk('public')->delete($item->image_path);
        $item->update(['image_path' => null]);

        return back()->with('success', 'Slide image removed.');
    }

    protected function validateSlider(Request $request, $ignoreId = null): array
    {
        $unique = 'unique:sliders,slug' . ($ignoreId ? ",{$ignoreId}" : '');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "nullable|string|max:255|{$unique}",
            'layout' => 'required|in:pure,with-content',
            'location' => 'required|in:header,footer,sidebar',
            'show_indicators' => 'sometimes|boolean',
            'show_arrows' => 'sometimes|boolean',
            'autoplay' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 1;
            while (
                Slider::where('slug', $slug)
                    ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                    ->exists()
            ) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

    /**
     * Save every slide from the request, handling new uploads and existing paths.
     */
    protected function saveItems(Request $request, Slider $slider): void
    {
        $items = $request->input('items', []);

        foreach ($items as $index => $item) {
            // 1) If user uploaded a new image
            if ($request->hasFile("items.{$index}.new_image")) {
                $path = $request
                    ->file("items.{$index}.new_image")
                    ->store('sliders', 'public');
            }
            // 2) Else if we have an existing path, reuse it
            elseif (!empty($item['existing_image_path'])) {
                $path = $item['existing_image_path'];
            }
            // 3) Otherwise no image → skip
            else {
                continue;
            }

            SliderItem::create([
                'slider_id' => $slider->id,
                'image_path' => $path,
                'content' => [
                    'title' => $item['content']['title'] ?? '',
                    'subtitle' => $item['content']['subtitle'] ?? '',
                    'buttons' => $item['content']['buttons'] ?? [],
                ],
                'sort_order' => $index,
            ]);
        }
    }
}