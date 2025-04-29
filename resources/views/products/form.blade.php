{{-- resources/views/products/form.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use App\Models\Media;

    $isEdit = isset($product);

    // 1) Initial featured images
    $initialFeatured = collect(old('featured_media_ids', []))
        ->map(fn($mid) => ($m = Media::find($mid)) ? ['id' => $m->id, 'url' => Storage::url($m->path)] : null)
        ->filter()
        ->values()
        ->toArray();

    if (empty($initialFeatured) && $isEdit && $product->featuredMedia->count()) {
        $initialFeatured = $product->featuredMedia
            ->map(fn($m) => ['id' => $m->id, 'url' => Storage::url($m->path)])
            ->toArray();
    }

    // 2) Categories list for checkboxes & parent dropdown
    $cats = $taxonomies->map(
        fn($t) => [
            'id' => $t->term_taxonomy_id,
            'name' => $t->term->name,
        ],
    );

    $selectedCats = old('taxonomy_ids', $isEdit ? $product->taxonomies->pluck('term_taxonomy_id')->toArray() : []);
@endphp

<form method="POST" action="{{ $isEdit ? route('products.update', $product->id) : route('products.store') }}"
    enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="productForm({ initial: {{ json_encode($initialFeatured) }} })">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- LEFT PANEL: Name & Description --}}
    <div class="lg:col-span-2 space-y-6">
        <div>
            <label class="block text-sm font-medium">Product Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                class="w-full border rounded p-2 @error('name') border-red-500 @enderror" />
            @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" rows="6"
                class="w-full border rounded p-2 @error('description') border-red-500 @enderror">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="space-y-6">

        {{-- 1) Categories (search + AJAX “Add” + parent) --}}
        <div x-data="categoryBox({
            cats: {{ $cats->toJson() }},
            selected: {{ json_encode($selectedCats) }}
        })" class="border rounded-lg p-4 space-y-3">
            <input type="text" x-model="search" placeholder="Search categories…"
                class="w-full border rounded px-3 py-2 text-sm" />

            <div class="flex justify-between items-center">
                <span class="font-semibold">Categories</span>
                <button type="button" @click="showAdd = !showAdd" class="text-blue-600 text-sm"
                    x-text="showAdd ? '- Add' : '+ Add'"></button>
            </div>

            <template x-if="showAdd">
                <div class="space-y-2">
                    <input type="text" x-model="newName" placeholder="New category"
                        class="w-full border rounded px-2 py-1 text-sm" />
                    <select x-model="parentId" class="w-full border rounded px-2 py-1 text-sm">
                        <option value="">— Parent —</option>
                        <template x-for="c in all" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="add()"
                        class="w-full bg-blue-600 text-white rounded px-3 py-2 text-sm">Add Category</button>
                </div>
            </template>

            <div class="max-h-40 overflow-y-auto space-y-1">
                <template x-for="cat in filtered" :key="cat.id">
                    <label class="flex items-center space-x-2 text-sm">
                        <input type="checkbox" :value="cat.id" x-model="selected" name="taxonomy_ids[]" />
                        <span x-text="cat.name"></span>
                    </label>
                </template>
            </div>
            @error('taxonomy_ids.*')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 2) Price --}}
        <div>
            <label class="block text-sm font-medium">Price</label>
            <input type="text" name="price" value="{{ old('price', $product->price ?? '') }}"
                class="w-full border rounded p-2 @error('price') border-red-500 @enderror" />
            @error('price')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 3) Stock --}}
        <div>
            <label class="block text-sm font-medium">Stock</label>
            <input type="text" name="stock" value="{{ old('stock', $product->stock ?? '') }}"
                class="w-full border rounded p-2 @error('stock') border-red-500 @enderror" />
            @error('stock')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 4) Status --}}
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="w-full border rounded p-2 @error('status') border-red-500 @enderror">
                <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>Active
                </option>
                <option value="0" {{ old('status', $product->status ?? 0) == 0 ? 'selected' : '' }}>Inactive
                </option>
            </select>
            @error('status')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 5) Featured Images --}}
        <div class="border rounded-lg p-4 space-y-3">
            <span class="block text-sm font-medium">Featured Images</span>
            <div class="flex flex-wrap gap-2">
                <template x-for="(img, i) in images" :key="img.id">
                    <div class="relative w-24 h-24">
                        <img :src="img.url" class="w-full h-full object-cover rounded border" />
                        <button type="button" @click="remove(i)"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs">×</button>
                    </div>
                </template>
                <button type="button" @click="openMedia()"
                    class="w-24 h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">+</button>
            </div>
            <template x-for="img in images" :key="img.id">
                <input type="hidden" name="featured_media_ids[]" :value="img.id" />
            </template>
            @error('featured_media_ids.*')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 6) Submit --}}
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
            {{ $isEdit ? 'Update Product' : 'Create Product' }}
        </button>
    </div>
</form>

{{-- Include the media-browser modal --}}
@include('media.browser-modal')

<script>
    function categoryBox({
        cats,
        selected
    }) {
        return {
            all: cats,
            selected: selected,
            search: '',
            showAdd: false,
            newName: '',
            parentId: '',

            get filtered() {
                return this.all.filter(c =>
                    c.name.toLowerCase().includes(this.search.toLowerCase())
                );
            },

            async add() {
                if (!this.newName.trim()) return;

                try {
                    let res = await fetch("{{ route('admin.products.categories.store') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            name: this.newName.trim(),
                            parent: this.parentId || null
                        })
                    });

                    if (res.status === 409) {
                        let err = await res.json();
                        return alert(err.message);
                    }
                    if (!res.ok) {
                        let err = await res.json().catch(() => null);
                        throw new Error(err?.message || res.statusText);
                    }

                    let cat = await res.json(); // { id, name }
                    this.all.push(cat);
                    this.selected.push(cat.id);
                    this.newName = '';
                    this.parentId = '';
                    this.showAdd = false;
                } catch (e) {
                    console.error(e);
                    alert("Error: " + e.message);
                }
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
                                if (!this.images.find(x => x.id === i.id)) {
                                    this.images.push(i);
                                }
                            });
                        }
                    }
                }));
            },

            remove(i) {
                this.images.splice(i, 1);
            }
        }
    }
</script>
