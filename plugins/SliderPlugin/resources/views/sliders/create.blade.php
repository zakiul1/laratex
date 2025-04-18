@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-900 rounded shadow" x-data="sliderForm()">
        <h2 class="text-2xl font-bold mb-4">Create New Slider</h2>

        <form method="POST" action="{{ route('slider-plugin.sliders.store') }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="w-full mt-1 p-2 border rounded" />
            </div>

            <!-- Subtitle -->
            <div>
                <label class="block text-sm font-medium">Subtitle</label>
                <input type="text" name="subtitle" class="w-full mt-1 p-2 border rounded" />
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-medium">Content</label>
                <textarea name="content" rows="4" class="w-full mt-1 p-2 border rounded"></textarea>
            </div>

            <!-- Layout -->
            <div>
                <label class="block text-sm font-medium">Layout</label>
                <select name="layout" class="w-full mt-1 p-2 border rounded">
                    <option value="one-column">One Column</option>
                    <option value="two-column">Two Column</option>
                </select>
            </div>

            <!-- Image Position -->
            <div>
                <label class="block text-sm font-medium">Image Position</label>
                <select name="image_position" class="w-full mt-1 p-2 border rounded">
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                </select>
            </div>

            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-medium">Upload Images</label>
                <input type="file" name="images[]" multiple @change="handleImages" class="mt-1" />
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

            <!-- Button Text + URL -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Button Text</label>
                    <input type="text" name="button_text" class="w-full mt-1 p-2 border rounded" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Button URL</label>
                    <input type="text" name="button_url" class="w-full mt-1 p-2 border rounded" />
                </div>
            </div>

            <!-- Toggles -->
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="show_arrows" checked class="mr-2" />
                    Show Arrows
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="show_indicators" checked class="mr-2" />
                    Show Indicators
                </label>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Slider</button>
            </div>
        </form>
    </div>

    <!-- Alpine JS -->
    <script>
        function sliderForm() {
            return {
                previews: [],
                handleImages(event) {
                    const files = event.target.files;
                    this.previews = [];
                    for (let i = 0; i < files.length; i++) {
                        this.previewFile(files[i]);
                    }
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
@endsection