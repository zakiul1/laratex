@php
$initialImage = old('featured_image')
    ? asset('storage/' . old('featured_image'))
    : (isset($page->featured_image) ? asset('storage/' . $page->featured_image) : '');

$metaDefaults = [];

if (old('meta_keys')) {
    foreach (old('meta_keys') as $i => $key) {
        $metaDefaults[] = ['key' => $key, 'value' => old('meta_values')[$i] ?? ''];
    }
} elseif (isset($page) && $page->metas) {
    $metaDefaults = $page->metas;
} else {
    $metaDefaults = [['key' => '', 'value' => '']];
}
@endphp

<div class="max-w-7xl mx-auto px-4 py-6">
    <form method="POST" action="{{ isset($page) ? route('pages.update', $page->id) : route('pages.store') }}"
        enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if(isset($page)) @method('PUT') @endif

        <!-- Left Panel -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Title -->
            <div>
                <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" placeholder="Add title"
                    class="w-full text-3xl font-semibold border-0 focus:ring-0 placeholder:text-gray-400" required />
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <textarea name="content" rows="12" placeholder="Type / to choose a block"
                    class="w-full border rounded p-4 text-sm text-gray-800">{{ old('content', $page->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Right Panel -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Status</h3>
                <select name="status" class="w-full border rounded p-2">
                    <option value="draft" {{ old('status', $page->status ?? 'draft') === 'draft' ? 'selected' : '' }}>
                        Draft</option>
                    <option value="published" {{ old('status', $page->status ?? '') === 'published' ? 'selected' : '' }}>
                        Published</option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Template -->
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Template</h3>
                <select name="template" class="w-full border rounded p-2">
                    <option value="">Default</option>
                    @foreach ($templateFiles as $template)
                        <option value="{{ $template }}" {{ old('template', $page->template ?? '') === $template ? 'selected' : '' }}>
                            {{ ucfirst($template) }}
                        </option>
                    @endforeach
                </select>
                @error('template')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}"
                    class="w-full border rounded p-2" placeholder="Optional. Auto-generated if blank." />
                @error('slug')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hidden type -->
            <input type="hidden" name="type" value="page" />

            <!-- Featured Image -->
            <div x-data="{ imagePreview: '{{ $initialImage }}' }" class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Featured Image</h3>
                <template x-if="imagePreview">
                    <img :src="imagePreview" class="w-full h-32 object-cover rounded border mb-2" />
                </template>
                <input type="file" name="featured_image" @change="
                    const file = $event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => imagePreview = e.target.result;
                        reader.readAsDataURL(file);
                    }" class="w-full text-sm" />
                @error('featured_image')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Meta Fields -->
        <div x-data="{ metas: [{ key: '', value: '' }] }" class="border rounded p-4 shadow bg-white">
            <h3 class="font-semibold text-sm mb-3">Meta Fields</h3>
            <template x-for="(meta, index) in metas" :key="index">
                <div class="flex gap-2 mb-2">
                    <input type="text" :name="`meta_keys[]`" x-model="meta.key" placeholder="Key"
                        class="w-1/2 border rounded p-2 text-sm" />
                    <input type="text" :name="`meta_values[]`" x-model="meta.value" placeholder="Value"
                        class="w-1/2 border rounded p-2 text-sm" />
                </div>
            </template>
            <button type="button" @click="metas.push({ key: '', value: '' })" class="text-xs text-blue-600 hover:underline">+
                Add Meta Field</button>
        </div>

            <!-- Submit -->
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
                    {{ isset($page) ? 'Update Page' : 'Publish Page' }}
                </button>
            </div>
        </div>
    </form>
</div>