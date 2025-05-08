{{-- resources/views/posts/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        use App\Models\Media;

        $isEdit = isset($post) && $post->exists;
        $formAction = $isEdit ? route('posts.update', $post) : route('posts.store');
        $formMethod = $isEdit ? 'PUT' : 'POST';

        // Seed initial featured images
        $initialFeatured = collect(old('featured_media_ids', $post->featured_images ?? []))
            ->map(fn($id) => ($m = Media::find($id)) ? ['id' => $m->id, 'url' => $m->getUrl('thumbnail')] : null)
            ->filter()
            ->values()
            ->toArray();

        if (empty($initialFeatured) && $isEdit) {
            $initialFeatured = $post
                ->getFeaturedMediaAttribute()
                ->map(fn($m) => ['id' => $m->id, 'url' => $m->getUrl('thumbnail')])
                ->toArray();
        }

        // Categories for Alpine
        $categoriesJson = $allCategories
            ->map(
                fn($c) => [
                    'id' => $c->term_taxonomy_id,
                    'name' => $c->term->name,
                    'parent' => $c->parent,
                ],
            )
            ->toJson();
        $selectedJson = json_encode(old('categories', $selected));

        // Custom meta
        $initialMetaFields = old('meta', $customMeta ?? []);
    @endphp

    <script>
        window.categoriesBoxData = {!! $categoriesJson !!};
        window.initialSelected = {!! $selectedJson !!};
        window.initialFeatured = @json($initialFeatured);
        window.initialMetaFields = @json($initialMetaFields);
    </script>

    <div>
        <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
            class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="postForm({ initial: window.initialFeatured })">
            @csrf
            @if ($formMethod === 'PUT')
                @method('PUT')
            @endif

            {{-- Validation Summary --}}
            @if ($errors->any())
                <div class="col-span-full bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-4">
                    <strong>There were some problems with your input:</strong>
                    <ul class="list-disc ml-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Left Panel --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Title --}}
                <div>
                    <input type="text" name="title" placeholder="Add title" required
                        value="{{ old('title', $post->title ?? '') }}"
                        class="w-full text-3xl font-semibold border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} focus:border-primary focus:ring-0 placeholder:text-gray-400">
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
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Title</label>
                            <input type="text" name="seo[title]" value="{{ old('seo.title', $seo['title'] ?? '') }}"
                                class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Description</label>
                            <textarea name="seo[description]" class="w-full border rounded p-2">{{ old('seo.description', $seo['description'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Meta Keywords</label>
                            <input type="text" name="seo[keywords]"
                                value="{{ old('seo.keywords', $seo['keywords'] ?? '') }}"
                                class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Robots</label>
                            <select name="seo[robots]" class="w-full border rounded p-2">
                                @foreach (['Index & Follow', 'NoIndex & Follow', 'NoIndex & NoFollow', 'No Archive', 'No Snippet'] as $opt)
                                    <option value="{{ $opt }}"
                                        {{ old('seo.robots', $seo['robots'] ?? '') === $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel --}}
            <div class="space-y-6">
                {{-- Status --}}
                <div class="border rounded p-4 bg-white shadow">
                    <h3 class="font-semibold text-sm mb-2">Status & Visibility</h3>
                    <select name="status"
                        class="w-full border rounded p-2 {{ $errors->has('status') ? 'border-red-500' : '' }}">
                        <option value="published"
                            {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>
                            Published</option>
                        <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>Draft
                        </option>
                    </select>
                    @error('status')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
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
                        placeholder="Optional. Auto-generated if blank."
                        class="w-full border rounded p-2 {{ $errors->has('slug') ? 'border-red-500' : '' }}">
                    @error('slug')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Post Type --}}
                <div class="border rounded p-4 bg-white shadow">
                    <h3 class="font-semibold text-sm mb-2">Post Type</h3>
                    <select name="type"
                        class="w-full border rounded p-2 {{ $errors->has('type') ? 'border-red-500' : '' }}">
                        <option value="post" {{ old('type', $post->type ?? '') === 'post' ? 'selected' : '' }}>Post
                        </option>
                        <option value="page" {{ old('type', $post->type ?? '') === 'page' ? 'selected' : '' }}>Page
                        </option>
                        <option value="custom" {{ old('type', $post->type ?? '') === 'custom' ? 'selected' : '' }}>Custom
                        </option>
                    </select>
                    @error('type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Categories --}}
                <div class="border rounded p-4 bg-white shadow" x-data="categoryBox(window.categoriesBoxData, window.initialSelected)">
                    <h3 class="font-semibold text-sm mb-2">Categories</h3>
                    <input x-model="search" type="text" placeholder="Search categories…"
                        class="w-full mb-2 border rounded p-2 text-sm">
                    <div class="max-h-48 overflow-y-auto mb-4 space-y-1">
                        <template x-for="cat in filteredList" :key="cat.id">
                            <label class="flex items-center text-sm">
                                <input type="checkbox" name="categories[]" :value="cat.id" x-model="selected"
                                    class="mr-2">
                                <span x-text="cat.indent + cat.name"></span>
                            </label>
                        </template>
                    </div>
                    @error('categories')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Featured Images --}}
                <div class="border rounded p-4 bg-white shadow">
                    <h3 class="font-semibold text-sm mb-2">Featured Images</h3>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <template x-for="(img, i) in $data.images" :key="img.id">
                            <div class="relative w-full h-24">
                                <img :src="img.url" class="w-full h-full object-cover rounded border">
                                <button type="button" @click="$data.removeImage(i)"
                                    class="absolute top-1 right-1 bg-white rounded-full text-red-600 hover:bg-red-100">×</button>
                            </div>
                        </template>
                        <button type="button" @click="$data.openMedia()"
                            class="w-full h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">+
                        </button>
                    </div>
                    @error('featured_media_ids')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <template x-for="img in $data.images" :key="img.id">
                        <input type="hidden" name="featured_media_ids[]" :value="img.id">
                    </template>
                </div>

                {{-- Custom Meta Fields --}}
                <div class="border rounded p-4 bg-white shadow" x-data="metaFields()">
                    <h3 class="font-semibold text-sm mb-2">Custom Meta Fields</h3>
                    <button type="button" @click="addField()" class="text-xs text-blue-600 mb-2">+ Add</button>
                    <div class="space-y-2">
                        <template x-for="(field, idx) in fields" :key="idx">
                            <div class="flex space-x-2 items-center">
                                <input type="text" :name="`meta[${idx}][key]`" x-model="field.key"
                                    placeholder="Meta Key" class="w-1/2 border rounded p-2 text-sm">
                                <input type="text" :name="`meta[${idx}][value]`" x-model="field.value"
                                    placeholder="Meta Value" class="w-1/2 border rounded p-2 text-sm">
                                <button type="button" @click="removeField(idx)"
                                    class="text-red-600 hover:text-red-800">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded shadow">
                    {{ $isEdit ? 'Update Post' : 'Publish Post' }}
                </button>
            </div>
        </form>

        @include('media.browser-modal')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>
    <script>
        function postForm({
            initial
        }) {
            return {
                images: initial || [],

                init() {},

                openMedia() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: true,
                            onSelect: items => {
                                const arr = Array.isArray(items) ? items : [items];
                                arr.forEach(i => {
                                    if (!this.images.find(x => x.id === i.id)) {
                                        this.images.push({
                                            id: i.id,
                                            // use the thumbnail URL your browser returns
                                            url: i.thumbnail
                                        });
                                    }
                                });
                            }
                        }
                    }));
                },

                removeImage(index) {
                    this.images.splice(index, 1);
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('categoryBox', (all, selected) => ({
                all,
                selected,
                search: '',
                newName: '',
                newParent: '',
                showAdd: false,

                get flatList() {
                    let roots = this.all.filter(c => c.parent === 0),
                        subs = this.all.filter(c => c.parent !== 0),
                        out = [];
                    roots.forEach(r => {
                        out.push({
                            ...r,
                            indent: ''
                        });
                        subs
                            .filter(s => s.parent === r.id)
                            .forEach(s => out.push({
                                ...s,
                                indent: '— '
                            }));
                    });
                    return out;
                },

                get filteredList() {
                    return this.flatList.filter(c =>
                        c.name.toLowerCase().includes(this.search.toLowerCase())
                    );
                },

                async addCategory() {
                    if (!this.newName.trim()) return;
                    let token = document.querySelector('meta[name="csrf-token"]').content;
                    let res = await fetch('{{ route('admin.posts.categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            name: this.newName,
                            parent: this.newParent || null
                        })
                    });
                    if (res.status === 409) {
                        alert((await res.json()).message);
                        return;
                    }
                    if (!res.ok) throw new Error('Failed to create category');
                    let json = await res.json();
                    this.all.push(json);
                    this.selected.push(json.id);
                    this.newName = '';
                    this.newParent = '';
                }
            }));

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
