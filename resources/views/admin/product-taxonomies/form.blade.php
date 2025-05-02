{{-- resources/views/admin/product-taxonomies/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        $isEdit = isset($taxonomy) && $taxonomy instanceof \App\Models\TermTaxonomy;
    @endphp

    <form method="POST"
        action="{{ $isEdit
            ? route('product-taxonomies.update', $taxonomy->term_taxonomy_id)
            : route('product-taxonomies.store') }}"
        x-data='taxonomyForm(@json($initialImages))' @media-selected.window="onMediaSelected($event.detail)"
        class="space-y-6">
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
                <option value="1" {{ old('status', $isEdit ? $taxonomy->status : 1) == 1 ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ old('status', $isEdit ? $taxonomy->status : 1) == 0 ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
            @error('status')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Featured Images --}}
        <div class="space-y-2">
            <label class="block font-semibold mb-1">Featured Images</label>
            <div class="flex flex-wrap gap-4">
                {{-- Thumbnails --}}
                <template x-for="(img, idx) in images" :key="img.id">
                    <div class="relative w-24 h-24 border rounded overflow-hidden">
                        <img :src="img.url" class="object-cover w-full h-full" alt="Image preview" />
                        <button type="button" @click="removeImage(idx)"
                            class="absolute top-0 right-0 bg-red-600 text-white rounded-bl px-1 text-xs">×</button>
                    </div>
                </template>

                {{-- Add button --}}
                <button type="button" @click="openMediaLibrary()"
                    class="w-24 h-24 flex items-center justify-center border-2 border-dashed rounded text-gray-500 hover:bg-gray-100">+</button>
            </div>

            {{-- Hidden inputs --}}
            <template x-for="img in images" :key="img.id">
                <input type="hidden" name="image_ids[]" :value="img.id" />
            </template>
            @error('image_ids')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded">
                {{ $isEdit ? 'Update' : 'Create' }} Category
            </button>
        </div>
    </form>

    @include('media.browser-modal')
@endsection

@push('scripts')
    <script>
        /**
         * Alpine component factory.
         * @param {Array<{id:number,url:string}>} initialImages
         */
        function taxonomyForm(initialImages) {
            return {
                images: initialImages.slice(),

                /**
                 * Handler for when media is selected.
                 */
                onMediaSelected(item) {
                    if (!this.images.find(i => i.id === item.id)) {
                        this.images.push({
                            id: item.id,
                            url: item.url
                        });
                    }
                },

                /**
                 * Open the media library modal.
                 * Provide both callback & onSelect for compatibility.
                 */
                openMediaLibrary() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: true,
                            callback: item => this.onMediaSelected(item),
                            onSelect: item => this.onMediaSelected(item)
                        }
                    }));
                },

                /**
                 * Remove an image by index.
                 */
                removeImage(idx) {
                    this.images.splice(idx, 1);
                }
            }
        }
    </script>
@endpush
