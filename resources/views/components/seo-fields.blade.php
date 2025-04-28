@php
    // ── Normalize SEO meta_value into $seo array ─────────────────────
    $raw = optional($model->seoMeta)->meta_value;

    if (is_string($raw)) {
        // If it’s a JSON string, decode it
        $seo = json_decode($raw, true) ?: [];
    } elseif (is_array($raw)) {
        // If it’s already an array (e.g. cast in your model), use it
        $seo = $raw;
    } else {
        // Fallback to empty array
        $seo = [];
    }
@endphp

<div class="border rounded p-4 bg-white shadow">
    <h3 class="font-semibold text-sm mb-2">SEO Settings</h3>

    <div class="space-y-4">
        <!-- Title -->
        <div>
            <label for="seo_title" class="block text-sm font-medium text-gray-700">
                SEO Title
            </label>
            <input type="text" id="seo_title" name="seo[title]" value="{{ old('seo.title', $seo['title'] ?? '') }}"
                class="w-full border rounded p-2 text-sm">
            @error('seo.title')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="seo_description" class="block text-sm font-medium text-gray-700">
                SEO Description
            </label>
            <textarea id="seo_description" name="seo[description]" class="w-full border rounded p-2 text-sm">{{ old('seo.description', $seo['description'] ?? '') }}</textarea>
            @error('seo.description')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keywords -->
        <div>
            <label for="seo_keywords" class="block text-sm font-medium text-gray-700">
                SEO Keywords
            </label>
            <input type="text" id="seo_keywords" name="seo[keywords]"
                value="{{ old('seo.keywords', $seo['keywords'] ?? '') }}" class="w-full border rounded p-2 text-sm">
            @error('seo.keywords')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Robots -->
        <div>
            <label for="seo_robots" class="block text-sm font-medium text-gray-700">
                Robots
            </label>
            <select id="seo_robots" name="seo[robots]" class="w-full border rounded p-2 text-sm">
                @foreach (['Index & Follow', 'NoIndex & Follow', 'NoIndex & NoFollow', 'No Archive', 'No Snippet'] as $opt)
                    <option value="{{ $opt }}"
                        {{ old('seo.robots', $seo['robots'] ?? '') === $opt ? 'selected' : '' }}>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
            @error('seo.robots')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
