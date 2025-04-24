@php
    // true only if this is an existing Page (edit)
    $isEdit = isset($page) && $page->exists;

    // ensure we have templates array
    $templates = $templates ?? getThemeTemplates();
@endphp

<div class="mx-auto">
    <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">
    <form method="POST" action="{{ $isEdit ? route('pages.update', $page) : route('pages.store') }}"
        enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- Left Panel --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Title --}}
            <div>
                <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}"
                    placeholder="Add title"
                    class="w-full text-3xl font-semibold border border-gray-300 focus:border-primary focus:ring-0 placeholder:text-gray-400"
                    required />
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Content (block-builder) --}}
            <div>
                <div id="customAreaBuilder" class="w-full   bg-white"></div>
                <!-- hidden JSON payload -->
                <textarea id="layoutData" name="content" class="hidden">{{ old('content', $page->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror

            </div>



            {{-- SEO Meta Fields --}}
            @include('components.seo-fields', ['model' => $page])
        </div>

        {{-- Right Panel --}}
        <div class="space-y-6">
            {{-- Status --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Status</h3>
                <select name="status" class="w-full border rounded p-2">
                    <option value="published"
                        {{ old('status', $page->status ?? 'published') === 'published' ? 'selected' : '' }}>
                        Published
                    </option>
                    <option value="draft" {{ old('status', $page->status ?? '') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Template --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Template</h3>
                <select name="template" class="w-full border rounded p-2">
                    @foreach ($templates as $key => $label)
                        <option value="{{ $key }}"
                            {{ old('template', $page->template ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('template')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Permalink --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}"
                    class="w-full border rounded p-2" placeholder="Optional. Auto-generated if blank." />
                @error('slug')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Hidden type field --}}
            <input type="hidden" name="type" value="page" />

            {{-- Featured Image --}}
            @php
                $initialImage = old('featured_image')
                    ? asset('storage/' . old('featured_image'))
                    : (isset($page->featured_image)
                        ? asset('storage/' . $page->featured_image)
                        : '');
            @endphp
            <div x-data="{ imagePreview: '{{ $initialImage }}' }" class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Featured Image</h3>
                <template x-if="imagePreview">
                    <img :src="imagePreview" class="w-full h-32 object-cover rounded border mb-2" />
                </template>
                <input type="file" name="featured_image"
                    @change="
                        const file = $event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => imagePreview = e.target.result;
                            reader.readAsDataURL(file);
                        }"
                    class="w-full text-sm" />
                @error('featured_image')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>



            {{-- Submit --}}
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
                    {{ $isEdit ? 'Update Page' : 'Publish Page' }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>
@endpush
