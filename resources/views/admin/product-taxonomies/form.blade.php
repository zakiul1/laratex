{{-- resources/views/admin/product-taxonomies/form.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use App\Models\Media;
    use App\Models\TermTaxonomy;

    $isEdit = isset($taxonomy) && $taxonomy instanceof TermTaxonomy;

    // Prepare initial images for Alpine
    $initialImages = collect(old('image_ids', []))
        ->map(fn($mid) => ($m = Media::find($mid)) ? ['id' => $mid, 'url' => $m->url()] : null)
        ->filter()
        ->values()
        ->toArray();

    if (empty($initialImages) && $isEdit && $taxonomy->images) {
        $initialImages = $taxonomy->images
            ->map(fn($img) => ['id' => $img->id, 'url' => Storage::url($img->path)])
            ->toArray();
    }
@endphp

<form method="POST"
    action="{{ $isEdit ? route('product-taxonomies.update', $taxonomy->term_taxonomy_id) : route('product-taxonomies.store') }}"
    enctype="multipart/form-data" x-data="taxonomyForm({{ json_encode($initialImages) }})" class="space-y-6">

    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- Name --}}
    <div>
        <label class="block font-semibold mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $isEdit ? $taxonomy->term->name : '') }}"
            class="w-full border rounded p-2" required />
        @error('name')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Slug --}}
    <div>
        <label class="block font-semibold mb-1">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $isEdit ? $taxonomy->term->slug : '') }}"
            class="w-full border rounded p-2" />
        @error('slug')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Parent --}}
    <div>
        <label class="block font-semibold mb-1">Parent Category</label>
        <select name="parent" class="w-full border rounded p-2">
            <option value="">None</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->term_taxonomy_id }}"
                    {{ old('parent', $isEdit ? $taxonomy->parent : '') == $parent->term_taxonomy_id ? 'selected' : '' }}>
                    {{ $parent->term->name }}
                </option>
            @endforeach
        </select>
        @error('parent')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $isEdit ? $taxonomy->description : '') }}</textarea>
        @error('description')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="block font-semibold mb-1">Status</label>
        <select name="status" class="w-full border rounded p-2">
            <option value="1" {{ old('status', $isEdit ? $taxonomy->status : 1) == 1 ? 'selected' : '' }}>Active
            </option>
            <option value="0" {{ old('status', $isEdit ? $taxonomy->status : 1) == 0 ? 'selected' : '' }}>Inactive
            </option>
        </select>
        @error('status')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Featured Images --}}
    <div class="space-y-2">
        <label class="block font-semibold mb-1">Featured Images</label>

        <div class="flex flex-wrap gap-4">
            <template x-for="(img, index) in images" :key="index">
                <div class="relative w-24 h-24 border rounded overflow-hidden">
                    <img :src="img.url" class="object-cover w-full h-full" />
                    <button type="button" @click="removeImage(index)"
                        class="absolute top-0 right-0 bg-red-600 text-white rounded-bl px-1 text-xs">✕</button>
                </div>
            </template>

            <button type="button" @click="openMediaLibrary"
                class="w-24 h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">
                +
            </button>
        </div>

        {{-- Hidden inputs for IDs --}}
        <template x-for="img in images" :key="img.id">
            <input type="hidden" name="image_ids[]" :value="img.id" />
        </template>

        @error('image_ids')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    {{-- Submit --}}
    <div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded">
            {{ $isEdit ? 'Update' : 'Create' }} Category
        </button>
    </div>
</form>

{{-- Include the media browser modal --}}
@include('media.browser-modal')

<script>
    function taxonomyForm(initialImages = []) {
        return {
            images: initialImages,
            openMediaLibrary() {
                // Dispatch on document so mediaBrowser() can catch it
                document.dispatchEvent(new CustomEvent('media-open', {
                    detail: {
                        onSelect: item => this.images.push({
                            id: item.id,
                            url: item.url
                        }),
                        multiple: true
                    }
                }));
            },
            removeImage(index) {
                this.images.splice(index, 1);
            }
        };
    }
</script>
