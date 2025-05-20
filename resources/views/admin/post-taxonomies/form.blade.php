{{-- resources/views/admin/post-taxonomies/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        $isEdit = isset($taxonomy) && $taxonomy instanceof \App\Models\TermTaxonomy;

        // Prepare the initial images array exactly like in posts form:
        // [ { id: 12, url: '…/storage/…/thumb.jpg' }, … ]
        $initialImages = [];

        if ($isEdit) {
            $initialImages = $taxonomy->images
                ->map(
                    fn($img) => [
                        'id' => $img->media_id,
                        'url' => Storage::url($img->path),
                    ],
                )
                ->toArray();
        }
    @endphp

    {{-- Expose to Alpine --}}
    <script>
        window.initialTaxonomyImages = @json($initialImages);
    </script>

    <form method="POST"
        action="{{ $isEdit ? route('post-taxonomies.update', $taxonomy->term_taxonomy_id) : route('post-taxonomies.store') }}"
        class="space-y-6" enctype="multipart/form-data" x-data="taxonomyForm({ initial: window.initialTaxonomyImages })">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- Name --}}
        <div>
            <label class="block font-semibold mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $isEdit ? $taxonomy->term->name : '') }}"
                class="w-full border rounded p-2" required>
            @error('name')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="block font-semibold mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $isEdit ? $taxonomy->term->slug : '') }}"
                class="w-full border rounded p-2">
            @error('slug')
                <p class="text-red-500 text-sm">{{ $message }}</p>
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
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $isEdit ? $taxonomy->description : '') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm">{{ $message }}</p>
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
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Featured Images --}}
        <div class="border rounded p-4 bg-white shadow">
            <h3 class="font-semibold text-sm mb-2">Featured Images</h3>

            <div class="grid grid-cols-4 gap-2 mb-4">
                <template x-for="(img, i) in images" :key="img.id">
                    <div class="relative w-full h-24">
                        <img :src="img.url" class="w-full h-full object-cover rounded border" alt="">
                        <button type="button" @click="removeImage(i)"
                            class="absolute top-1 right-1 bg-white rounded-full text-red-600 hover:bg-red-100">
                            ×
                        </button>
                    </div>
                </template>

                <button type="button" @click="openMedia()"
                    class="w-full h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">
                    +
                </button>
            </div>

            @error('image_ids')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <template x-for="img in images" :key="img.id">
                <input type="hidden" name="image_ids[]" :value="img.id">
            </template>
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded">
                {{ $isEdit ? 'Update Category' : 'Create Category' }}
            </button>
        </div>
    </form>

    @include('media.browser-modal')
@endsection

@push('scripts')
    <script>
        /**
         * Alpine component factory for post-taxonomy featured images,
         * using the same pattern as your posts form.
         */
        function taxonomyForm({
            initial = []
        }) {
            return {
                images: initial.slice(),

                openMedia() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: true,
                            onSelect: items => {
                                // media browser may return single or array
                                let arr = Array.isArray(items) ? items : [items];
                                arr.forEach(i => {
                                    if (!this.images.find(x => x.id === i.id)) {
                                        this.images.push({
                                            id: i.id,
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
            }
        }
    </script>
@endpush
