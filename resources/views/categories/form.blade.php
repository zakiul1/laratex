@php
    // Determine if this is edit (existing) or create
    $isEdit = isset($category) && $category->exists;
    $formRoute = $isEdit ? route('categories.update', $category) : route('categories.store');
@endphp

<form action="{{ $formRoute }}" method="POST" enctype="multipart/form-data" x-data="categoryForm()">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Panel -->
        <div class="w-full lg:w-1/2 space-y-6">

            <div class="border p-4 mb-6 bg-gray-50 rounded">
                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
                        class="w-full border border-gray-300 rounded mt-1 p-2 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror


                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}"
                        class="w-full border border-gray-300 rounded mt-1 p-2 @error('slug') border-red-500 @enderror">
                    @error('slug')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent Category -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Parent Category</label>
                    <select name="parent_id"
                        class="w-full border border-gray-300 rounded mt-1 p-2 @error('parent_id') border-red-500 @enderror">
                        <option value="">-- None --</option>
                        @foreach ($allCategories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" required
                        class="w-full border border-gray-300 rounded mt-1 p-2 @error('status') border-red-500 @enderror">
                        <option value="1" @selected(old('status', $category->status ?? '1') == '1')>Active</option>
                        <option value="0" @selected(old('status', $category->status ?? '') == '0')>Inactive</option>
                    </select>
                    @error('status')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Featured Image Upload + Preview -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Featured Image</label>
                    <input type="file" name="featured_image" @change="handleImage($event)"
                        class="w-full mt-1 text-sm border-gray-300">

                    <!-- New upload preview -->
                    <div class="mt-3 relative w-32 h-32" x-show="preview">
                        <img :src="preview" alt="Preview" class="w-full h-full object-cover border rounded">
                        <button type="button" @click="clearImage"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs">&times;</button>
                    </div>

                    <!-- Existing image if editing -->
                    @if ($isEdit && $category->featured_image)
                        <div class="mt-3 relative w-32 h-32" x-show="!preview">
                            <img src="{{ asset('storage/' . $category->featured_image) }}" alt="Current"
                                class="w-full h-full object-cover border rounded">
                        </div>
                    @endif

                    @error('featured_image')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>


                <!-- Submit Button -->
                <div class="mt-4">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm shadow">
                        {{ $isEdit ? 'Update' : 'Create' }} Category
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full lg:w-1/2 space-y-6">




            <!-- SEO Meta Fields -->
            @include('components.seo-fields', ['model' => $category])
        </div>
    </div>

</form>

<script>
    function categoryForm() {
        return {
            preview: '',
            handleImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => this.preview = e.target.result;
                    reader.readAsDataURL(file);
                }
            },
            clearImage() {
                this.preview = '';
                document.querySelector('input[type="file"][name="featured_image"]').value = '';
            }
        };
    }
</script>
