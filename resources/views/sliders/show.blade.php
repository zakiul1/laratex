@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow" x-data="imageUploader()" @dragover.prevent
        @drop.prevent="dropFiles($event)">

        <h2 class="text-xl font-semibold mb-4">Slider Preview</h2>

        <!-- Slider Info -->
        <div class="space-y-2 text-sm mb-6">
            <div><strong>Title:</strong> {{ $slider->title }}</div>
            <div><strong>Subtitle:</strong> {{ $slider->subtitle }}</div>
            <div><strong>Content:</strong> {{ $slider->content }}</div>
            <div><strong>Button:</strong> {{ $slider->button_text }} → {{ $slider->button_url }}</div>
            <div><strong>Layout:</strong> {{ ucfirst($slider->layout) }}</div>
            <div><strong>Image Position:</strong> {{ ucfirst($slider->image_position) }}</div>
            <div><strong>Arrows:</strong> {{ $slider->show_arrows ? 'Yes' : 'No' }}</div>
            <div><strong>Indicators:</strong> {{ $slider->show_indicators ? 'Yes' : 'No' }}</div>
            <div><strong>Location:</strong> {{ ucfirst($slider->slider_location) }}</div>
        </div>

        <!-- Existing Images -->
        <div class="mb-6">
            <h3 class="text-sm font-medium mb-2">Existing Images</h3>
            <div class="flex flex-wrap gap-3">
                @foreach ($slider->images as $image)
                    <div class="relative w-28 h-28">
                        <img src="{{ asset('storage/' . $image->path) }}" class="w-full h-full object-cover rounded border" />
                        <button onclick="deleteImage({{ $image->id }}, this)"
                            class="absolute top-1 right-1 w-6 h-6 text-xs rounded-full bg-red-600 text-white">×</button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Drag & Drop Upload -->
        <div class="border-2 border-dashed border-gray-300 rounded p-6 text-center cursor-pointer"
            @click="$refs.fileInput.click()" x-ref="dropzone">
            <p class="text-gray-500 text-sm">Click or drag and drop images here (Max 5MB each)</p>
            <input type="file" multiple class="hidden" x-ref="fileInput" @change="handleFiles($event)">
        </div>

        <!-- Error Messages -->
        <template x-if="errors.length > 0">
            <div class="mt-4 bg-red-100 text-red-800 p-3 rounded text-sm">
                <ul>
                    <template x-for="err in errors">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Image Preview -->
        <div class="flex flex-wrap gap-4 mt-4">
            <template x-for="(img, index) in previews" :key="index">
                <div class="relative w-28 h-28">
                    <img :src="img" class="w-full h-full object-cover rounded border" />
                    <button @click="removeImage(index)"
                        class="absolute top-1 right-1 bg-red-600 text-white text-xs w-6 h-6 rounded-full">×</button>
                </div>
            </template>
        </div>

        <!-- Submit Button -->
        <button @click="uploadImages" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            Upload Selected Images
        </button>
    </div>

    <!-- Alpine Script -->
    <script>
        function imageUploader() {
            return {
                files: [],
                previews: [],
                errors: [],
                sliderId: {{ $slider->id }},

                handleFiles(event) {
                    this.errors = [];
                    this.files = Array.from(event.target.files);
                    this.previews = [];

                    this.files.forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            this.errors.push(`${file.name} is not an image`);
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            this.errors.push(`${file.name} exceeds 5MB limit`);
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = e => this.previews.push(e.target.result);
                        reader.readAsDataURL(file);
                    });
                },

                dropFiles(event) {
                    const droppedFiles = event.dataTransfer.files;
                    this.$refs.fileInput.files = droppedFiles;
                    this.handleFiles({ target: { files: droppedFiles } });
                },

                removeImage(index) {
                    this.previews.splice(index, 1);
                    this.files.splice(index, 1);
                },

                uploadImages() {
                    this.errors = [];

                    if (!this.files.length) {
                        this.errors.push('No files selected');
                        return;
                    }

                    const formData = new FormData();
                    this.files.forEach(file => formData.append('images[]', file));

                    fetch(`/slider-images/upload/${this.sliderId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                this.errors.push(data.message || 'Upload failed');
                            }
                        })
                        .catch(() => {
                            this.errors.push('Something went wrong');
                        });
                }
            }
        }

        function deleteImage(id, el) {
            if (confirm('Delete this image?')) {
                fetch(`/slider-images/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) el.closest('.relative').remove();
                        else alert('Delete failed');
                    });
            }
        }
    </script>
@endsection