@php
    $isEdit = isset($product);
@endphp

<form method="POST" action="{{ $isEdit ? route('products.update', $product->id) : route('products.store') }}"
    enctype="multipart/form-data" x-data="productForm()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <!-- Left: Name, Description -->
    <div class="lg:col-span-2 space-y-6">
        <div>
            <label class="block text-sm font-medium">Product Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                class="w-full border rounded mt-1 p-2 @error('name') border-red-500 @enderror" />
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" rows="6"
                class="w-full border rounded p-2 @error('description') border-red-500 @enderror">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Right Panel -->
    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium">Category</label>
            <select name="category_id" class="w-full border rounded p-2 @error('category_id') border-red-500 @enderror">
                <option value="">Select Category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Price</label>
            <input type="text" name="price" value="{{ old('price', $product->price ?? '') }}"
                class="w-full border rounded p-2 @error('price') border-red-500 @enderror" />
            @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Stock</label>
            <input type="text" name="stock" value="{{ old('stock', $product->stock ?? '') }}"
                class="w-full border rounded p-2 @error('stock') border-red-500 @enderror" />
            @error('stock') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="w-full border rounded p-2 @error('status') border-red-500 @enderror">
                <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $product->status ?? 0) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Featured Image -->
        <div
            x-data="{ image: '{{ isset($product->featured_image) ? asset('storage/' . $product->featured_image) : '' }}' }">
            <label class="block text-sm font-medium">Featured Image</label>
            <template x-if="image">
                <img :src="image" class="w-full h-32 object-cover rounded border mb-2" />
            </template>
            <input type="file" name="featured_image" @change="
                const file = $event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => image = e.target.result;
                    reader.readAsDataURL(file);
                }" class="w-full text-sm" />
            @error('featured_image') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Gallery Images -->
        <div>
            <label class="block text-sm font-medium">Upload Gallery Images</label>
            <input type="file" name="images[]" multiple
                class="w-full text-sm @error('images.*') border-red-500 @enderror" @change="handleFiles($event)" />
            <div class="flex flex-wrap gap-2 mt-3">
                <template x-for="(img, index) in previews" :key="index">
                    <div class="relative w-24 h-24">
                        <img :src="img" class="w-full h-full object-cover rounded border" />
                        <button type="button" @click="removeImage(index)"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs">×</button>
                    </div>
                </template>
            </div>
            @error('images.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            @if ($isEdit && $product->images->count())
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Existing Images</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($product->images as $img)
                            <div class="relative w-24 h-24">
                                <img src="{{ asset('storage/' . $img->image) }}"
                                    class="w-full h-full object-cover rounded border" />
                                <button type="button" onclick="deleteProductImage({{ $img->id }}, this)"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs">×</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
            {{ $isEdit ? 'Update Product' : 'Create Product' }}
        </button>
    </div>
</form>

<script>
    function productForm() {
        return {
            previews: [],
            handleFiles(event) {
                this.previews = [];
                const files = Array.from(event.target.files);
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => this.previews.push(e.target.result);
                    reader.readAsDataURL(file);
                });
            },
            removeImage(index) {
                this.previews.splice(index, 1);
            }
        }
    }

    function deleteProductImage(id, el) {
        if (!confirm('Delete this image?')) return;

        fetch(`/product-images/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    el.closest('.relative').remove();
                } else {
                    alert('Failed to delete image');
                }
            });
    }
</script>