{{-- resources/views/posts/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        $isEdit = isset($post) && $post->exists;
        $formAction = $isEdit ? route('posts.update', $post) : route('posts.store');
        $formMethod = $isEdit ? 'PUT' : 'POST';

        // Category data for your categoryBox
        $templates = $templates ?? getThemeTemplates();
        $allCategories = $allCategories ?? collect();
        $selected = $selected ?? [];

        $categoriesJson = $allCategories
            ->map(
                fn($c) => [
                    'id' => $c->id,
                    'name' => $c->term->name,
                    'parent' => $c->parent,
                ],
            )
            ->toJson();
        $selectedJson = json_encode($selected);

        // Initial arrays for multi-image picker (empty on create)
        $initialImageIds = old('featured_images', $post->featured_images ?? []);
        $initialImageUrls = old('featured_images_urls', $post->featured_images_urls ?? []);
    @endphp

    <script>
        window.categoriesBoxData = {!! $categoriesJson !!};
        window.initialSelected = {!! $selectedJson !!};
        window.initialImageIds = {!! json_encode($initialImageIds) !!};
        window.initialImageUrls = {!! json_encode($initialImageUrls) !!};
    </script>

    <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @csrf
        @if ($formMethod === 'PUT')
            @method('PUT')
        @endif

        {{-- ── Left: Title & Block Editor ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Title --}}
            <div>
                <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" placeholder="Add title"
                    required
                    class="w-full text-3xl font-semibold border border-gray-300 focus:border-primary focus:ring-0 placeholder:text-gray-400">
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Block Editor --}}
            <div id="customAreaBuilder" class="w-full border rounded"></div>
            <textarea id="layoutData" name="content" class="hidden">{{ old('content', $post->content ?? '') }}</textarea>
            @error('content')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            {{-- SEO Fields --}}
            @include('components.seo-fields', ['model' => $post])
        </div>

        {{-- ── Right: Options Panel ──────────────────────────────────── --}}
        <div class="space-y-6">
            {{-- Status & Visibility --}}
            <div class="border rounded p-4 bg-white shadow">
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
            <div class="border rounded p-4 bg-white shadow">
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
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
                    placeholder="Optional. Auto-generated if blank." class="w-full border rounded p-2">
            </div>

            {{-- Post Type --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Post Type</h3>
                <select name="type" class="w-full border rounded p-2">
                    <option value="post" {{ old('type', $post->type ?? '') === 'post' ? 'selected' : '' }}>Post
                    </option>
                    <option value="page" {{ old('type', $post->type ?? '') === 'page' ? 'selected' : '' }}>Page
                    </option>
                    <option value="custom" {{ old('type', $post->type ?? '') === 'custom' ? 'selected' : '' }}>Custom
                    </option>
                </select>
            </div>

            {{-- Categories Meta-Box --}}
            <div class="border rounded p-4 bg-white shadow" x-data="categoryBox(window.categoriesBoxData, window.initialSelected)">
                <input x-model="search" type="text" placeholder="Search categories…"
                    class="w-full mb-2 border rounded p-2 text-sm">

                <h3 class="font-semibold text-sm mb-2 flex justify-between items-center">
                    Categories
                    <button type="button" class="text-xs text-blue-600" @click="showAdd = !showAdd">
                        <span x-text="showAdd ? '−' : '+'"></span> Add
                    </button>
                </h3>

                <div class="max-h-48 overflow-y-auto mb-4 space-y-1">
                    <template x-for="cat in filteredList" :key="cat.id">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="categories[]" :value="cat.id" x-model="selected"
                                class="mr-2">
                            <span x-text="cat.indent + cat.name"></span>
                        </label>
                    </template>
                </div>

                <div x-show="showAdd" class="space-y-2">
                    <input x-model="newName" type="text" placeholder="New category name"
                        class="w-full border rounded p-2 text-sm">
                    <select x-model="newParent" class="w-full border rounded p-2 text-sm">
                        <option value="">— Parent —</option>
                        <template x-for="cat in filteredList" :key="cat.id">
                            <option :value="cat.id" x-text="cat.indent + cat.name"></option>
                        </template>
                    </select>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                        @click="addCategory()">Add Category</button>
                </div>
            </div>

            {{-- ── Featured Images Picker ────────────────────────────────── --}}
            <div class="border rounded p-4 bg-white shadow" x-data="featuredImagesPicker()">
                <h3 class="font-semibold text-sm mb-2">Featured Images</h3>

                {{-- Preview grid --}}
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <template x-for="(url, idx) in previewUrls" :key="idx">
                        <div class="relative">
                            <img :src="url" class="w-full h-24 object-cover rounded border">
                            <button type="button" @click="remove(idx)"
                                class="absolute top-1 right-1 bg-white rounded-full text-red-600 hover:bg-red-100">×</button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="openMedia()"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm mb-2">Add Images</button>

                {{-- hidden inputs for submission --}}
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="featured_images[]" :value="id">
                </template>
            </div>

            {{-- Publish Button --}}
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">{{ $isEdit ? 'Update Post' : 'Publish Post' }}</button>
            </div>
        </div>
    </form>

    {{-- include the shared media browser modal --}}
    @include('media.browser-modal')
@endsection

@push('scripts')
    {{-- Alpine.js (once in layout, this can be omitted if already in your dashboard layout) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Block-editor bundle --}}
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>

    {{-- categoryBox component --}}
    <script>
        function categoryBox(all, selected) {
            return {
                all,
                selected,
                search: '',
                showAdd: false,
                newName: '',
                newParent: '',
                get flatList() {
                    let roots = this.all.filter(c => c.parent === 0),
                        subs = this.all.filter(c => c.parent !== 0),
                        out = [];
                    roots.forEach(r => {
                        out.push({
                            ...r,
                            indent: ''
                        });
                        subs.filter(s => s.parent === r.id)
                            .forEach(s => out.push({
                                ...s,
                                indent: '— '
                            }));
                    });
                    return out;
                },
                get filteredList() {
                    let term = this.search.toLowerCase();
                    return this.flatList.filter(c => c.name.toLowerCase().includes(term));
                },
                async addCategory() {
                    if (!this.newName.trim()) return;
                    let token = document.querySelector('meta[name=csrf-token]').content;
                    let res = await fetch('{{ route('categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            name: this.newName,
                            parent: this.newParent,
                            status: 1
                        })
                    });
                    if (!res.ok) {
                        alert('Failed to add category');
                        return;
                    }
                    let json = await res.json();
                    this.all.push({
                        id: json.id,
                        name: json.term.name,
                        parent: json.parent
                    });
                    this.newName = '';
                    this.newParent = '';
                    this.showAdd = false;
                }
            };
        }
    </script>

    {{-- featuredImagesPicker component --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('featuredImagesPicker', () => ({
                selectedIds: window.initialImageIds,
                previewUrls: window.initialImageUrls,
                openMedia() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            onSelect: img => {
                                if (!this.selectedIds.includes(img.id)) {
                                    this.selectedIds.push(img.id);
                                    this.previewUrls.push(img.url);
                                }
                            }
                        }
                    }));
                },
                remove(i) {
                    this.selectedIds.splice(i, 1);
                    this.previewUrls.splice(i, 1);
                }
            }));
        });
    </script>
@endpush
