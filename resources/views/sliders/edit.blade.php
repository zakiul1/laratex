@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow" x-data="sliderForm()">
        <h2 class="text-xl font-semibold mb-4">Edit Slider</h2>

        <form action="{{ route('sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium">Title</label>
                    <input type="text" name="title" value="{{ $slider->title }}"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="block text-sm font-medium">Subtitle</label>
                    <input type="text" name="subtitle" value="{{ $slider->subtitle }}"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Content -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Content</label>
                    <textarea name="content" rows="3"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $slider->content }}</textarea>
                </div>

                <!-- Button Text / URL -->
                <div>
                    <label class="block text-sm font-medium">Button Text</label>
                    <input type="text" name="button_text" value="{{ $slider->button_text }}"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Button URL</label>
                    <input type="url" name="button_url" value="{{ $slider->button_url }}"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Layout & Image Position -->
                <div>
                    <label class="block text-sm font-medium">Layout</label>
                    <select name="layout"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="one-column" @selected($slider->layout === 'one-column')>One Column</option>
                        <option value="two-column" @selected($slider->layout === 'two-column')>Two Column</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Image Position</label>
                    <select name="image_position"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="left" @selected($slider->image_position === 'left')>Left</option>
                        <option value="right" @selected($slider->image_position === 'right')>Right</option>
                    </select>
                </div>

                <!-- Show Arrows -->
                <div>
                    <label class="block text-sm font-medium">Show Arrows</label>
                    <input type="hidden" name="show_arrows" value="0">
                    <input type="checkbox" name="show_arrows" value="1" class="mt-1" @if(old('show_arrows', $slider->show_arrows ?? false)) checked @endif />
                </div>

                <!-- Show Indicators -->
                <div>
                    <label class="block text-sm font-medium">Show Indicators</label>
                    <input type="hidden" name="show_indicators" value="0">
                    <input type="checkbox" name="show_indicators" value="1" class="mt-1" @if(old('show_indicators', $slider->show_indicators ?? false)) checked @endif />
                </div>

                <!-- Slider Location -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Slider Location</label>
                    <select name="slider_location"
                        class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="header" @selected($slider->slider_location === 'header')>Header</option>
                        <option value="footer" @selected($slider->slider_location === 'footer')>Footer</option>
                        <option value="homepage" @selected($slider->slider_location === 'homepage')>Homepage</option>
                        <option value="sidebar" @selected($slider->slider_location === 'sidebar')>Sidebar</option>
                    </select>
                </div>

                <!-- Existing Images -->
                @if ($slider->images && count($slider->images))
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Existing Images</label>
                        <div class="flex flex-wrap gap-4 mt-2">
                            @foreach ($slider->images as $image)
                                <div class="relative w-32 h-32">
                                    <img src="{{ asset('storage/' . $image->image) }}"
                                        onerror="this.src='https://via.placeholder.com/150'"
                                        class="w-full h-full object-cover rounded border" />
                                    <button type="button" onclick="deleteImage({{ $image->id }}, this)"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs">×</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Upload New Images -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Upload New Images</label>
                    <input type="file" multiple name="images[]" x-ref="input" class="block"
                        @change="previewImages($event)" />
                    <div class="flex flex-wrap mt-4 gap-4">
                        <template x-for="(image, index) in previews" :key="index">
                            <div class="relative w-32 h-32">
                                <img :src="image" class="w-full h-full object-cover rounded border" />
                                <button type="button" @click="removeImage(index)"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs">×</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                Update Slider
            </button>
        </form>
    </div>

    <!-- Alpine Script -->
    <script>
        function sliderForm() {
            return {
                previews: [],
                files: [],

                previewImages(event) {
                    this.previews = [];
                    this.files = Array.from(event.target.files);

                    this.files.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = e => this.previews.push(e.target.result);
                        reader.readAsDataURL(file);
                    });

                    const dt = new DataTransfer();
                    this.files.forEach(file => dt.items.add(file));
                    this.$refs.input.files = dt.files;
                },

                removeImage(index) {
                    this.previews.splice(index, 1);
                    this.files.splice(index, 1);

                    const dt = new DataTransfer();
                    this.files.forEach(file => dt.items.add(file));
                    this.$refs.input.files = dt.files;
                }
            }
        }

        function deleteImage(id, el) {


            fetch(`/admin/slider-images/${id}`, {
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
                        alert('Failed to delete image.');
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Error deleting image. Check console.');
                });
        }
    </script>
@endsection