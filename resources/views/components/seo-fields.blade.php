{{-- resources/views/components/seo-fields.blade.php --}}
@php
    // merge old input (on validation error) or existing model data
    $meta = old('seo', $model->seoMeta->meta ?? []);
@endphp

<div class="border p-4 mb-6 bg-gray-50 rounded">
    <h2 class="font-semibold mb-4">SEO Meta</h2>

    {{-- Meta Title --}}
    <div class="mb-4">
        <label for="seo_title" class="block text-sm font-medium">Meta Title</label>
        <input id="seo_title" type="text" name="seo[title]" value="{{ $meta['title'] ?? '' }}"
            class="mt-1 w-full border rounded p-2">
    </div>

    {{-- Robots Option --}}
    <div class="mb-4">
        <label for="seo_robots" class="block text-sm font-medium">Robots Option</label>
        <select id="seo_robots" name="seo[robots]" class="mt-1 w-full border rounded p-2">
            @foreach (['Index & Follow', 'NoIndex & Follow', 'NoIndex & NoFollow', 'No Archive', 'No Snippet'] as $option)
                <option value="{{ $option }}" {{ ($meta['robots'] ?? '') === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Meta Description --}}
    <div class="mb-4">
        <label for="seo_description" class="block text-sm font-medium">Meta Description</label>
        <textarea id="seo_description" name="seo[description]" rows="3" class="mt-1 w-full border rounded p-2">{{ $meta['description'] ?? '' }}</textarea>
    </div>

    {{-- Meta Keywords --}}
    <div class="mb-4">
        <label for="seo_keywords" class="block text-sm font-medium">Meta Keywords</label>
        <textarea id="seo_keywords" name="seo[keywords]" rows="2" class="mt-1 w-full border rounded p-2">{{ $meta['keywords'] ?? '' }}</textarea>
    </div>
</div>
