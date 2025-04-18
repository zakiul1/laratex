@php
    /** @var \Plugins\SliderPlugin\Models\Slider|null $slider */
    $isEdit      = isset($slider);
    $formRoute   = $isEdit
        ? route('slider-plugin.sliders.update', $slider->id)
        : route('slider-plugin.sliders.store');
    $formMethod  = $isEdit ? 'PUT' : 'POST';

    // initial previews for Alpine (URLs only)
    $initialPreviews = $isEdit
        ? $slider->images->map(fn($img) => asset('storage/' . $img->file_path))->values()
        : collect();
@endphp

<form  x-data="sliderForm({{ $initialPreviews->toJson() }})"
       method="POST"
       action="{{ $formRoute }}"
       enctype="multipart/form-data"
       class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <!-- Title -->
    <div>
        <label class="block text-sm font-medium">Title</label>
        <input  type="text"
                name="title"
                value="{{ old('title', $slider->title ?? '') }}"
                class="w-full mt-1 p-2 border rounded" />
    </div>

    <!-- Subtitle -->
    <div>
        <label class="block text-sm font-medium">Subtitle</label>
        <input  type="text"
                name="subtitle"
                value="{{ old('subtitle', $slider->subtitle ?? '') }}"
                class="w-full mt-1 p-2 border rounded" />
    </div>

    <!-- Content -->
    <div>
        <label class="block text-sm font-medium">Content</label>
        <textarea name="content" rows="4" class="w-full mt-1 p-2 border rounded">{{ old('content', $slider->content ?? '') }}</textarea>
    </div>

    <!-- Layout + Position -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Layout</label>
            <select name="layout" class="w-full mt-1 p-2 border rounded">
                <option value="one-column" @selected(old('layout', $slider->layout ?? '')==='one-column')>One Column</option>
                <option value="two-column" @selected(old('layout', $slider->layout ?? '')==='two-column')>Two Column</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Image Position</label>
            <select name="image_position" class="w-full mt-1 p-2 border rounded">
                <option value="left"  @selected(old('image_position', $slider->image_position ?? '')==='left')>Left</option>
                <option value="right" @selected(old('image_position', $slider->image_position ?? '')==='right')>Right</option>
            </select>
        </div>
    </div>

    <!-- Image upload & previews -->
    <div>
        <label class="block text-sm font-medium">Upload Images</label>
        <input type="file" name="images[]" multiple @change="handleImages" class="mt-1" />

        <!-- previews (new + existing) -->
        <div class="mt-2 flex flex-wrap gap-4">
            <template x-for="(img, index) in previews" :key="index">
                <div class="relative w-32 h-32">
                    <img :src="img" class="w-full h-full object-cover rounded border" />
                    <button type="button"
                            class="absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 text-xs"
                            @click="removeImage(index)">✕</button>
                </div>
            </template>
        </div>
    </div>

    <!-- Button -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Button Text</label>
            <input  type="text"
                    name="button_text"
                    value="{{ old('button_text', $slider->button_text ?? '') }}"
                    class="w-full mt-1 p-2 border rounded" />
        </div>

        <div>
            <label class="block text-sm font-medium">Button URL</label>
            <input  type="text"
                    name="button_url"
                    value="{{ old('button_url', $slider->button_url ?? '') }}"
                    class="w-full mt-1 p-2 border rounded" />
        </div>
    </div>

    <!-- Toggles -->
    <div class="flex items-center gap-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="show_arrows"
                   @checked(old('show_arrows', $slider->show_arrows ?? true)) class="mr-2" />
            Show Arrows
        </label>
        <label class="inline-flex items-center">
            <input type="checkbox" name="show_indicators"
                   @checked(old('show_indicators', $slider->show_indicators ?? true)) class="mr-2" />
            Show Indicators
        </label>
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        {{ $isEdit ? 'Update Slider' : 'Save Slider' }}
    </button>
</form>

@push('scripts')
    <script>
        function sliderForm(initial = []) {
            return {
                previews: initial,
                handleImages(evt) {
                    Array.from(evt.target.files).forEach(file => this.previewFile(file));
                },
                previewFile(file) {
                    const reader = new FileReader();
                    reader.onload = e => this.previews.push(e.target.result);
                    reader.readAsDataURL(file);
                },
                removeImage(index) {
                    this.previews.splice(index, 1);
                }
            }
        }
    </script>
@endpush
