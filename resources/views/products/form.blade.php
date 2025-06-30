@extends('layouts.dashboard')

@section('content')

    @php
        use App\Models\Media;

        $isEdit = isset($product);

        // Seed initial featured images (use the 'thumbnail' conversion)
        $initialFeatured = collect(old('featured_media_ids', []))
            ->map(fn($mid) => ($m = Media::find($mid)) ? ['id' => $m->id, 'url' => $m->getUrl('thumbnail')] : null)
            ->filter()
            ->values()
            ->toArray();

        if (empty($initialFeatured) && $isEdit && $product->featuredMedia->count()) {
            $initialFeatured = $product->featuredMedia
                ->map(fn($m) => ['id' => $m->id, 'url' => $m->getUrl('thumbnail')])
                ->toArray();
        }

        // Categories for the checkbox list
        $cats = $taxonomies->map(
            fn($t) => [
                'id' => $t->term_taxonomy_id,
                'name' => $t->term->name,
            ],
        );

        $selectedCats = old('taxonomy_ids', $isEdit ? $product->taxonomies->pluck('term_taxonomy_id')->toArray() : []);

        // ─── NEW: Seed initial SEO values ───
        // If old input exists (validation error), use old('seo'). Otherwise, if editing, decode from $product->seo. If new, default to [].
        $seo = old('seo', $isEdit ? $product->seo : []);
    @endphp

    <!-- Block Editor Styles -->
    <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

    <form method="POST" action="{{ $isEdit ? route('products.update', $product->id) : route('products.store') }}"
        enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="productForm({ initial: {{ json_encode($initialFeatured) }} })">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- Validation Summary --}}
        @if ($errors->any())
            <div class="col-span-full bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-4">
                <strong>Whoops—something went wrong:</strong>
                <ul class="list-disc ml-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- LEFT PANEL --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Product Name --}}
            <div>
                <label class="block text-sm font-medium">Product Name</label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                    class="w-full border rounded p-2 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Block Editor --}}
            <div>
                <div id="customAreaBuilder" class="w-full bg-white"></div>
                <textarea id="layoutData" name="content" class="hidden">{{ old('content', $product->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="6"
                    class="w-full border rounded p-2 @error('description') border-red-500 @enderror">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ─────────── NEW: SEO Settings ─────────── --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">SEO Settings</h3>
                <div class="space-y-4">
                    {{-- Meta Title --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Meta Title</label>
                        <input type="text" name="seo[title]" value="{{ old('seo.title', $seo['title'] ?? '') }}"
                            class="w-full border rounded p-2 @error('seo.title') border-red-500 @enderror">
                        @error('seo.title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Meta Description --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Meta Description</label>
                        <textarea name="seo[description]" class="w-full border rounded p-2 @error('seo.description') border-red-500 @enderror">{{ old('seo.description', $seo['description'] ?? '') }}</textarea>
                        @error('seo.description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Meta Keywords --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Meta Keywords</label>
                        <input type="text" name="seo[keywords]"
                            value="{{ old('seo.keywords', $seo['keywords'] ?? '') }}"
                            class="w-full border rounded p-2 @error('seo.keywords') border-red-500 @enderror">
                        @error('seo.keywords')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Robots --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Robots</label>
                        <select name="seo[robots]"
                            class="w-full border rounded p-2 @error('seo.robots') border-red-500 @enderror">
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
            {{-- ──────────────────────────────────────────────────── --}}
        </div>

        {{-- RIGHT PANEL --}}
        <div class="space-y-6">

            {{-- Categories --}}
            <div x-data="categoryBox({ cats: {{ $cats }}, selected: {{ json_encode($selectedCats) }} })" class="border rounded-lg p-4 space-y-3">
                <label class="block text-sm font-medium">Categories</label>

                {{-- Search --}}
                <input type="text" x-model="search" placeholder="Search categories…"
                    class="w-full border rounded px-3 py-2 text-sm mb-2">

                {{-- Add New --}}
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Select Categories</span>
                    <button type="button" @click="showAdd = !showAdd" class="text-blue-600 text-sm"
                        x-text="showAdd ? '– Hide' : '+ Add'"></button>
                </div>

                {{-- Add Form --}}
                <template x-if="showAdd">
                    <div class="space-y-2 mb-4">
                        <input type="text" x-model="newName" placeholder="New category name"
                            class="w-full border rounded px-2 py-1 text-sm">
                        <select x-model="newParent" class="w-full border rounded px-2 py-1 text-sm">
                            <option value="">— Parent —</option>
                            <template x-for="c in cats" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                        <button type="button" @click="addCategory()"
                            class="w-full bg-blue-600 text-white rounded px-3 py-2 text-sm">
                            Add Category
                        </button>
                    </div>
                </template>

                {{-- Checkbox List --}}
                <div class="max-h-40 overflow-y-auto space-y-1">
                    <template x-for="cat in filtered" :key="cat.id">
                        <label class="flex items-center space-x-2 text-sm">
                            <input type="checkbox" :value="cat.id" x-model="selected" name="taxonomy_ids[]">
                            <span x-text="cat.name"></span>
                        </label>
                    </template>
                </div>
                @error('taxonomy_ids')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- Slug --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Slug</h3>
                <input type="text" name="slug" value="{{ old('slug', $product->slug ?? '') }}"
                    placeholder="Optional. Auto-generated if blank."
                    class="w-full border rounded p-2 {{ $errors->has('slug') ? 'border-red-500' : '' }}">
                @error('slug')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Price --}}
            {{--        <div>
                <label class="block text-sm font-medium">Price</label>
                <input type="text" name="price" value="{{ old('price', $product->price ?? '') }}"
                    class="w-full border rounded p-2 @error('price') border-red-500 @enderror">
                @error('price')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div> --}}

            {{-- Stock --}}
            {{--      <div>
                <label class="block text-sm font-medium">Stock</label>
                <input type="text" name="stock" value="{{ old('stock', $product->stock ?? '') }}"
                    class="w-full border rounded p-2 @error('stock') border-red-500 @enderror">
                @error('stock')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div> --}}

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium">Status</label>
                <select name="status" class="w-full border rounded p-2 @error('status') border-red-500 @enderror">
                    <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ old('status', $product->status ?? 0) == 0 ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Featured Images --}}
            <div class="border rounded-lg p-4 space-y-3">
                <label class="block text-sm font-medium">Featured Images</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="(img, i) in images" :key="img.id">
                        <div class="relative w-24 h-24">
                            <img :src="img.url" class="w-full h-full object-cover rounded border">
                            <button type="button" @click="removeImage(i)"
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs">
                                ×
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="openMedia()"
                        class="w-24 h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">
                        +
                    </button>
                </div>
                <template x-for="img in images" :key="img.id">
                    <input type="hidden" name="featured_media_ids[]" :value="img.id">
                </template>
                @error('featured_media_ids')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
                {{ $isEdit ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </form>

    @include('media.browser-modal')

    @push('scripts')
        <script src="{{ asset('blockeditor/bundle.js') }}"></script>
    @endpush

    <script>
        function categoryBox({
            cats,
            selected
        }) {
            return {
                cats,
                selected,
                search: '',
                showAdd: false,
                newName: '',
                newParent: '',

                get filtered() {
                    return this.cats.filter(c =>
                        c.name.toLowerCase().includes(this.search.toLowerCase())
                    );
                },

                async addCategory() {
                    if (!this.newName.trim()) return;
                    let token = document.querySelector('meta[name=csrf-token]').content;
                    let res = await fetch("{{ route('admin.products.categories.store') }}", {
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
                        let err = await res.json();
                        return alert(err.message);
                    }
                    if (!res.ok) throw new Error('Failed to create category');
                    let cat = await res.json();
                    this.cats.push(cat);
                    this.selected.push(cat.id);
                    this.newName = '';
                    this.newParent = '';
                    this.showAdd = false;
                }
            }
        }

        function productForm({
            initial
        }) {
            return {
                images: initial,

                openMedia() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: true,
                            onSelect: items => {
                                const arr = Array.isArray(items) ? items : [items];
                                arr.forEach(i => {
                                    if (!this.images.find(x => x.id === i.id)) this.images.push(i);
                                });
                            }
                        }
                    }));
                },

                removeImage(i) {
                    this.images.splice(i, 1);
                }
            }
        }
    </script>
@endsection
