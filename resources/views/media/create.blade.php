@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Upload Media</h1>

        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" x-data="uploader()"
            @submit.prevent="submit()" class="space-y-6">
            @csrf

            {{-- Category selector (maps to your controller's category_id) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Category
                </label>
                <select name="category_id" x-model="category" class="w-1/3 border-gray-300 rounded px-2 py-1">
                    <option value="">Uncategorized</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->term_taxonomy_id }}">
                            {{ $cat->term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Drag & drop / click zone --}}
            <div id="dropzone"
                class="border-2 border-dashed border-gray-300 rounded p-8 text-center text-gray-500 cursor-pointer hover:border-purple-400 transition-colors"
                @drop.prevent="handleDrop($event)" @dragover.prevent @click="$refs.input.click()">
                <template x-if="files.length === 0">
                    <p>Drag & drop images here, or click to select</p>
                </template>

                {{-- Previews --}}
                <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <template x-for="(file, i) in files" :key="i">
                        <div class="relative rounded-lg overflow-hidden bg-gray-100">
                            <img :src="file.preview" class="w-full h-24 object-cover" alt="" />
                            <button @click.stop="remove(i)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600"
                                title="Remove">&times;</button>
                        </div>
                    </template>
                </div>

                <input x-ref="input" type="file" name="files[]" accept="image/*" multiple class="hidden"
                    @change="handleSelect($event)" />
            </div>

            {{-- Upload button --}}
            <button type="submit" :disabled="!files.length || isUploading"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded disabled:opacity-50 flex items-center gap-2">
                <span x-show="isUploading"
                    class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                Upload (<span x-text="files.length"></span>)
            </button>

            {{-- Error display --}}
            <p x-show="error" class="text-red-600 text-sm" x-text="error"></p>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function uploader() {
            return {
                files: [],
                category: '',
                isUploading: false,
                error: '',

                handleSelect(e) {
                    this.addFiles(e.target.files);
                    e.target.value = '';
                },

                handleDrop(e) {
                    this.addFiles(e.dataTransfer.files);
                },

                addFiles(list) {
                    this.error = '';
                    Array.from(list).forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            this.error = 'Only image files are allowed.';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            this.error = 'Each file must be smaller than 5MB.';
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = evt => {
                            this.files.push({
                                file,
                                preview: evt.target.result
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                },

                remove(index) {
                    this.files.splice(index, 1);
                },

                async submit() {
                    if (!this.files.length) return;
                    this.isUploading = true;
                    this.error = '';

                    const formData = new FormData();
                    this.files.forEach(f => formData.append('files[]', f.file));
                    if (this.category) formData.append('category_id', this.category);

                    try {
                        const res = await fetch('{{ route('admin.media.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData,
                        });
                        const json = await res.json();
                        if (!res.ok || json.error) {
                            throw new Error(json.error || 'Upload failed');
                        }
                        // on success, redirect back to index or clear form
                        window.location = '{{ route('admin.media.index') }}';
                    } catch (err) {
                        console.error(err);
                        this.error = err.message;
                    } finally {
                        this.isUploading = false;
                    }
                },
            }
        }
    </script>
@endpush
