@extends('layouts.dashboard')

@section('content')
        <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Create Slider</h2>

            <form action="{{ route('sliders.store') }}" method="POST" enctype="multipart/form-data" x-data="sliderForm()">
                @csrf
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
                    <div>
                        <label class="block text-sm font-medium">Title</label>
                        <input type="text" name="title"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Subtitle</label>
                        <input type="text" name="subtitle"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Content</label>
                        <textarea name="content" rows="3"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Button Text</label>
                        <input type="text" name="button_text"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Button URL</label>
                        <input type="url" name="button_url"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Layout</label>
                        <select name="layout"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="one-column">One Column</option>
                            <option value="two-column">Two Column</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Image Position</label>
                        <select name="image_position"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="left">Left</option>
                            <option value="right">Right</option>
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
    <!-- Status Checkbox -->
    <div>
        <label class="block text-sm font-medium">Active Status</label>
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" value="1" class="mt-1" @checked(old('status', $slider->status ?? true)) />
    </div>



                    <!-- ✅ SLIDER LOCATION: Now dropdown -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Slider Location</label>
                        <select name="slider_location"
                            class="w-full border border-gray-300 rounded mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                           <option value="homepage">Homepage</option>
                            <option value="footer">Footer</option>
                            
                            <option value="sidebar">Sidebar</option>
                        </select>
                    </div>

                    <!-- ✅ Image Upload With Preview -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Upload Images</label>
                        <input type="file" multiple name="images[]" class="block" @change="handleFiles($event)" />
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

                <button type="submit"
                    class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                    Save Slider
                </button>
            </form>
        </div>

        <script>
            function sliderForm() {
                return {
                    previews: [],
                    files: [],

                    handleFiles(event) {
                        this.previews = [];
                        this.files = Array.from(event.target.files);

                        this.files.forEach(file => {
                            const reader = new FileReader();
                            reader.onload = e => this.previews.push(e.target.result);
                            reader.readAsDataURL(file);
                        });
                    },

                    removeImage(index) {
                        this.previews.splice(index, 1);
                        this.files.splice(index, 1);
                        const input = document.querySelector('input[type="file"]');
                        const dataTransfer = new DataTransfer();
                        this.files.forEach(file => dataTransfer.items.add(file));
                        input.files = dataTransfer.files;
                    }
                }
            }
        </script>
@endsection