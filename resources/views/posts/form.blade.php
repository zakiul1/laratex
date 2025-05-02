{{-- resources/views/posts/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $isEdit = isset($post) && $post->exists;
        $formAction = $isEdit ? route('posts.update', $post) : route('posts.store');
        $formMethod = $isEdit ? 'PUT' : 'POST';

        // Categories data for box:
        $categoriesJson = $allCategories
            ->map(
                fn($c) => [
                    'id' => $c->term_taxonomy_id,
                    'name' => $c->term->name,
                    'parent' => $c->parent,
                ],
            )
            ->toJson();
        $selectedJson = json_encode($selected);

        // Featured images seed:
        $initialImageIds = old('featured_images', $featuredImages ?? []);
        $initialImageUrls = [];
        if (count($initialImageIds)) {
            $items = \App\Models\Media::whereIn('id', $initialImageIds)->get();
            $map = $items
                ->mapWithKeys(
                    fn($m) => [
                        $m->id => $m->getUrl('thumbnail'),
                    ],
                )
                ->toArray();
            foreach ($initialImageIds as $id) {
                if (isset($map[$id])) {
                    $initialImageUrls[] = $map[$id];
                }
            }
        }

        // Custom meta fields:
        $initialMetaFields = old('meta', $customMeta ?? []);

        // Featured media models (for editing):
        $initialFeatured = ($post->featured_media ?? collect())
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl('thumbnail'),
                ],
            )
            ->values()
            ->all();
    @endphp

    <script>
        window.categoriesBoxData = {!! $categoriesJson !!};
        window.initialSelected = {!! $selectedJson !!};
        window.initialImageIds = @json($initialImageIds);
        window.initialImageUrls = @json($initialImageUrls);
        window.initialMetaFields = @json($initialMetaFields);
        window.initialFeatured = @json($initialFeatured);
    </script>

    <div>
        <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
            class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @csrf
            @if ($formMethod === 'PUT')
                @method('PUT')
            @endif

            {{-- Left: Title, Editor, SEO --}}
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

                {{-- SEO Settings --}}
                <div class="border rounded p-4 bg-white shadow">
                    <h3 class="font-semibold text-sm mb-2">SEO Settings</h3>
                    <div class="space-y-4">
                        {{-- title, description, keywords, robots --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Title</label>
                            <input type="text" name="seo[title]" value="{{ $seo['title'] ?? '' }}"
                                class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Description</label>
                            <textarea name="seo[description]" class="w-full border rounded p-2">{{ $seo['description'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Keywords</label>
                            <input type="text" name="seo[keywords]" value="{{ $seo['keywords'] ?? '' }}"
                                class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Robots</label>
                            <select name="seo[robots]" class="w-full border rounded p-2">
                                @foreach (['Index & Follow', 'NoIndex & Follow', 'NoIndex & NoFollow', 'No Archive', 'No Snippet'] as $opt)
                                    <option value="{{ $opt }}"
                                        {{ ($seo['robots'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right: Settings, Categories, Featured Images, Meta --}}
            <div class="space-y-6">

                {{-- Status & Visibility --}}
                <div class="border rounded p-4 bg-white shadow">
                    <h3 class="font-semibold text-sm mb-2">Status & Visibility</h3>
                    <select name="status" class="w-full border rounded p-2">
                        <option value="published"
                            {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>
                            Published</option>
                        <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>Draft
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
                    <select name="type" class="w-full border rounded p-2"
                        onchange="if(this.value==='product'){window.location='{{ route('products.create') }}';}">
                        <option value="post" {{ old('type', $post->type ?? '') === 'post' ? 'selected' : '' }}>Post
                        </option>
                        <option value="page" {{ old('type', $post->type ?? '') === 'page' ? 'selected' : '' }}>Page
                        </option>
                        <option value="custom" {{ old('type', $post->type ?? '') === 'custom' ? 'selected' : '' }}>Custom
                        </option>
                        <option value="product">Product</option>
                    </select>
                </div>

                {{-- Categories Box --}}
                <div class="border rounded p-4 bg-white shadow" x-data="categoryBox(window.categoriesBoxData, window.initialSelected)">
                    <input x-model="search" type="text" placeholder="Search categories…"
                        class="w-full mb-2 border rounded p-2 text-sm">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-sm">Categories</h3>
                        <button type="button" class="text-xs text-blue-600" @click="showAdd = !showAdd">
                            <span x-text="showAdd? '−':'+'"></span> Add
                        </button>
                    </div>
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
                            <template x-for="cat in flatList" :key="cat.id">
                                <option :value="cat.id" x-text="cat.indent + cat.name"></option>
                            </template>
                        </select>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                            @click="addCategory()">Add Category</button>
                    </div>
                </div>

                {{-- Featured Images Picker --}}
                <div class="border rounded p-4 bg-white shadow" x-data="featuredImagesPicker()" x-init="init()">
                    <h3 class="font-semibold text-sm mb-2">Featured Images</h3>

                    {{-- Preview grid --}}
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <template x-for="(imgUrl, idx) in previewUrls" :key="idx">
                            <div class="relative">
                                <img :src="imgUrl" class="w-full h-24 object-cover rounded border" />
                                <button type="button" @click="remove(idx)"
                                    class="absolute top-1 right-1 bg-white rounded-full text-red-600 hover:bg-red-100">×</button>
                            </div>
                        </template>
                    </div>

                    {{-- Open media browser --}}
                    <button type="button" @click="openMedia()"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm mb-2">Add Images</button>

                    {{-- Hidden inputs --}}
                    <template x-for="id in filteredIds" :key="id">
                        <input type="hidden" name="featured_images[]" :value="id" />
                    </template>
                </div>

                {{-- Custom Meta Fields --}}
                <div class="border rounded p-4 bg-white shadow" x-data="metaFields()">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-sm">Custom Meta Fields</h3>
                        <button type="button" class="text-xs text-blue-600" @click="addField()">+ Add</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(field, index) in fields" :key="index">
                            <div class="flex space-x-2 items-center">
                                <input type="text" :name="`meta[${index}][key]`" x-model="field.key"
                                    placeholder="Meta Key" class="w-1/2 border rounded p-2 text-sm">
                                <input type="text" :name="`meta[${index}][value]`" x-model="field.value"
                                    placeholder="Meta Value" class="w-1/2 border rounded p-2 text-sm">
                                <button type="button" @click="removeField(index)"
                                    class="text-red-600 hover:text-red-800">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded shadow">{{ $isEdit ? 'Update Post' : 'Publish Post' }}</button>

            </div>
        </form>
    </div>

    {{-- include your shared media-browser modal --}}
    @include('media.browser-modal')
@endsection

@push('scripts')
    {{-- blockeditor bundle --}}
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>

    <script>
        document.addEventListener('alpine:init', () => {

            // Category Box
            Alpine.data('categoryBox', (all, selected) => ({
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
                        subs.filter(s => s.parent === r.id).forEach(s => out.push({
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
                    let res = await fetch('{{ route('admin.posts.categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            name: this.newName,
                            parent: this.newParent,
                            status: 1
                        })
                    });
                    if (res.status === 409) {
                        alert((await res.json()).message);
                        return;
                    }
                    if (!res.ok) throw new Error(await res.text());
                    let j = await res.json();
                    this.all.push({
                        id: j.id,
                        name: j.name,
                        parent: j.parent
                    });
                    this.selected.push(j.id);
                    this.newName = '';
                    this.newParent = '';
                    this.showAdd = false;
                }
            }));

            // Featured Images Picker
            Alpine.data('featuredImagesPicker', () => ({
                selectedIds: window.initialFeatured.map(f => f.id) || [],
                previewUrls: window.initialFeatured.map(f => f.url) || [],
                init() {
                    /* already seeded */
                },
                get filteredIds() {
                    return this.selectedIds.filter(i => Number.isInteger(+i));
                },
                openMedia() {
                    window.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            onSelect: this.addImage.bind(this)
                        }
                    }));
                },
                addImage(img) {
                    if (!this.selectedIds.includes(img.id)) {
                        this.selectedIds.push(img.id);
                        this.previewUrls.push(img.url);
                    }
                },
                remove(i) {
                    this.selectedIds.splice(i, 1);
                    this.previewUrls.splice(i, 1);
                }
            }));

            // Meta Fields
            Alpine.data('metaFields', () => ({
                fields: window.initialMetaFields || [],
                addField() {
                    this.fields.push({
                        key: '',
                        value: ''
                    });
                },
                removeField(i) {
                    this.fields.splice(i, 1);
                }
            }));

        });
    </script>
@endpush
