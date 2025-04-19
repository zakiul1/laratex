@extends('layouts.dashboard')

@section('content')
    @php
        $initialImage = old('featured_image')
            ? asset('storage/' . old('featured_image'))
            : (isset($post->featured_image)
                ? asset('storage/' . $post->featured_image)
                : '');

        $metaDefaults = [];
        if (old('meta_keys')) {
            foreach (old('meta_keys') as $i => $key) {
                $metaDefaults[] = ['key' => $key, 'value' => old('meta_values')[$i] ?? ''];
            }
        } elseif (isset($post) && $post->metas) {
            $metaDefaults = $post->metas;
        } else {
            $metaDefaults = [['key' => '', 'value' => '']];
        }

        $formAction = isset($post) ? route('posts.update', $post->id) : route('posts.store');
        $formMethod = isset($post) ? 'PUT' : 'POST';
        $templates = $templates ?? getThemeTemplates();
    @endphp
    <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @csrf
        @if ($formMethod === 'PUT')
            @method('PUT')
        @endif

        {{-- Left: Title, Main Content & Block Editor --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Title --}}
            <div>
                <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" placeholder="Add title"
                    class="w-full text-3xl font-semibold border border-gray-300 focus:border-primary focus:ring-0 placeholder:text-gray-400"
                    required />
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Main Content (CKEditor) --}}
            {{--
            <div>
                <label for="editor" class="sr-only">Content</label>
                <textarea id="editor" name="content" style="min-height:500px;"
                    class="w-full border border-gray-300 rounded p-3 focus:border-primary focus:ring-0" required>{{ old('content', $post->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            --}}


            {{-- inside your form, wherever you had the old #editor block --}}

            <!-- where the JS builder will mount itself -->
            <div id="customAreaBuilder" class="w-full border rounded bg-white" style="min-height:500px;"></div>
            <!-- hidden JSON payload -->
            <textarea id="layoutData" name="content" class="hidden">{{ old('content', $post->content ?? '') }}</textarea>
            @error('content')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror



        </div>

        {{-- Right: Options Panel --}}
        <div class="space-y-6">
            {{-- Status --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Status & Visibility</h3>
                <select name="status" class="w-full border rounded p-2">
                    <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>
                        Published
                    </option>
                    <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>
                </select>
            </div>

            {{-- Template --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Template</h3>
                <select name="template" class="w-full border rounded p-2">
                    <option value="">Default</option>
                    @foreach ($templates as $key => $label)
                        <option value="{{ $key }}"
                            {{ old('template', $post->template ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Permalink --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
                    class="w-full border rounded p-2" placeholder="Optional. Auto-generated if blank." />
            </div>

            {{-- Post Type --}}
            <div class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-2">Post Type</h3>
                <select name="type" class="w-full border rounded p-2">
                    <option value="post" {{ old('type', $post->type ?? '') === 'post' ? 'selected' : '' }}>
                        Post
                    </option>
                    <option value="page" {{ old('type', $post->type ?? '') === 'page' ? 'selected' : '' }}>
                        Page
                    </option>
                    <option value="custom" {{ old('type', $post->type ?? '') === 'custom' ? 'selected' : '' }}>
                        Custom
                    </option>
                </select>
            </div>

            {{-- Featured Image --}}
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
                        }
                    "
                    class="w-full text-sm" />
            </div>

            {{-- Meta Fields --}}
            <div x-data='{"metas": @json($metaDefaults)}' class="border rounded p-4 shadow bg-white">
                <h3 class="font-semibold text-sm mb-3">Meta Fields</h3>
                <template x-for="(meta, index) in metas" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="`meta_keys[]`" x-model="meta.key" placeholder="Key"
                            class="w-1/2 border rounded p-2 text-sm" />
                        <input type="text" :name="`meta_values[]`" x-model="meta.value" placeholder="Value"
                            class="w-1/2 border rounded p-2 text-sm" />
                    </div>
                </template>
                <button type="button" @click="metas.push({ key: '', value: '' })"
                    class="text-xs text-blue-600 hover:underline">+ Add Meta Field</button>
            </div>

            {{-- Publish Button --}}
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
                    {{ isset($post) ? 'Update Post' : 'Publish Post' }}
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>
@endpush
