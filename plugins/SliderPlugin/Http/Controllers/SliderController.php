<?php

namespace Plugins\SliderPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Plugins\SliderPlugin\Models\Slider;
use Plugins\SliderPlugin\Models\SliderItem;
use App\Models\Media;             // ← import your Laratex Media model

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

        $this->saveItems($request, $slider);

        return redirect()
            ->route('slider-plugin.sliders.index')
            ->with('success', 'Slider created successfully.');
    }

    public function edit($id)
    {
        $slider = Slider::with('items')->findOrFail($id);

        $items = old('items')
            ? old('items')
            : $slider->items->map(function (SliderItem $it) {
                return [
                    'id' => $it->id,
                    'existing_image_path' => $it->image_path,
                    'media_id' => $it->media_id,
                    'media_preview_url' => $it->media ? $it->media->getUrl() : null,
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

        // remove all old slides
        $slider->items()->delete();
        $this->saveItems($request, $slider);

        return back()->with('success', 'Slider updated successfully.');
    }

    public function destroy($id)
    {
        Slider::findOrFail($id)->delete();
        return back()->with('success', 'Slider removed.');
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
            // new fields:
            'heading' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
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
     * Save every slide from the request, handling:
     *  1) New local uploads
     *  2) Selected media-library images
     *  3) Existing paths (edit)
     */
    protected function saveItems(Request $request, Slider $slider): void
    {
        $items = $request->input('items', []);

        foreach ($items as $index => $item) {
            $path = null;
            $media_id = null;

            // 1) Local upload?
            if ($request->hasFile("items.{$index}.new_image")) {
                $path = $request
                    ->file("items.{$index}.new_image")
                    ->store('sliders', 'public');
            }
            // 2) Media-library selection?
            elseif (!empty($item['media_id'])) {
                $media_id = $item['media_id'];
                // grab path if you want legacy image_path
                if ($media = Media::find($media_id)) {
                    $path = $media->path; // or $media->getUrl() depending on how you store
                }
            }
            // 3) existing saved path
            elseif (!empty($item['existing_image_path'])) {
                $path = $item['existing_image_path'];
            }

            // if after all that we have neither a path nor a media_id, skip
            if (!$path && !$media_id) {
                continue;
            }

            SliderItem::create([
                'slider_id' => $slider->id,
                'image_path' => $path,
                'media_id' => $media_id,
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