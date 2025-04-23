@php
    use Illuminate\Support\Facades\Storage;

    // Precompute your media library JSON
    $mediaData = $items
        ->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => Storage::url($m->path),
                'filename' => $m->filename,
            ];
        })
        ->toJson();
@endphp

@extends('layouts.dashboard')

@section('content')
    <div x-data="mediaLibrary()" class="p-6 bg-gray-50 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Media Library</h2>

        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex space-x-2">
                <input x-ref="input" type="file" multiple @change="addFiles" class="hidden" />
                <button @click="$refs.input.click()"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">
                    <!-- upload icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v8M8 16h8" />
                    </svg>
                    Select Files
                </button>
                <button @click="uploadAll" :disabled="files.length === 0"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow disabled:opacity-50">
                    <!-- cloud-upload icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3-8 4 16 3-8h4" />
                    </svg>
                    <span x-text="'Upload ' + files.length"></span>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <input type="text" x-model="searchTerm" placeholder="Search media…"
                    class="border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />

                <button @click="viewMode='grid'"
                    :class="{ 'text-indigo-600': viewMode === 'grid', 'text-gray-400': viewMode !== 'grid' }"
                    class="hover:text-indigo-600">
                    <!-- grid icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4h6v6H4V4zm0 10h6v6H4v-6zm10-10h6v6h-6V4zm0 10h6v6h-6v-6z" />
                    </svg>
                </button>

                <button @click="viewMode='list'"
                    :class="{ 'text-indigo-600': viewMode === 'list', 'text-gray-400': viewMode !== 'list' }"
                    class="hover:text-indigo-600">
                    <!-- list icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Selected File Previews --}}
        <div class="flex space-x-4 overflow-x-auto mb-8 pb-2">
            <template x-for="(f, idx) in files" :key="idx">
                <div
                    class="relative w-40 h-32 flex-shrink-0 bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                    <img :src="f.preview" class="w-full h-full object-cover" />

                    <!-- close button -->
                    <button @click="remove(idx)"
                        class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-md focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- progress bar -->
                    <div class="absolute bottom-0 left-0 w-full">
                        <div class="h-1 bg-gray-200">
                            <div class="h-full bg-indigo-500" :style="`width:${f.progress}%`"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Media Grid or List --}}
        <div>
            <template x-if="viewMode==='grid'">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    <template x-for="item in filtered" :key="item.id">
                        <div class="relative group bg-white rounded-lg shadow overflow-hidden">
                            <img :src="item.url" class="w-40 h-40 object-cover group-hover:opacity-75 transition" />

                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-25 transition"></div>

                            <button @click="showModal(item)"
                                class="absolute top-2 left-2 bg-white bg-opacity-75 p-1 rounded opacity-0 group-hover:opacity-100 transition">
                                <!-- eye icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-800" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.269 2.943 9.542 7-1.273 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            <button @click="deleteMedia(item.id)"
                                class="absolute top-2 right-2 bg-red-500 p-1 rounded opacity-0 group-hover:opacity-100 transition">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="p-2 text-xs truncate" x-text="item.filename"></div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="viewMode==='list'">
                <div class="space-y-2">
                    <template x-for="item in filtered" :key="item.id">
                        <div class="flex items-center justify-between bg-white rounded-lg shadow p-3 group">
                            <div class="flex items-center gap-4">
                                <img :src="item.url" class="w-12 h-12 object-cover rounded" />
                                <span x-text="item.filename" class="text-sm font-medium"></span>
                            </div>
                            <button @click="deleteMedia(item.id)" class="text-red-500 hover:text-red-700 transition">
                                Delete
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Image Preview Modal --}}
        <div x-show="modalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-lg w-full">
                <div class="flex justify-end p-2">
                    <button @click="modalOpen=false"
                        class="text-gray-600 hover:text-gray-900 text-xl leading-none">&times;</button>
                </div>
                <img :src="modalImage.url" class="w-full h-auto" />
                <div class="p-4 text-center font-medium" x-text="modalImage.filename"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function mediaLibrary() {
            return {
                files: [],
                library: {!! $mediaData !!},
                viewMode: 'grid',
                searchTerm: '',
                modalOpen: false,
                modalImage: {},

                get filtered() {
                    if (!this.searchTerm) return this.library;
                    return this.library.filter(i =>
                        i.filename.toLowerCase().includes(this.searchTerm.toLowerCase())
                    );
                },

                addFiles(e) {
                    for (let f of e.target.files) {
                        if (!f.type.startsWith('image/')) continue;
                        let reader = new FileReader();
                        reader.onload = evt => {
                            this.files.push({
                                file: f,
                                name: f.name,
                                preview: evt.target.result,
                                progress: 0
                            });
                        };
                        reader.readAsDataURL(f);
                    }
                    e.target.value = null;
                },

                remove(idx) {
                    this.files.splice(idx, 1);
                },

                uploadAll() {
                    let total = this.files.length;
                    this.files.forEach((f, idx) => {
                        let form = new FormData();
                        form.append('files[]', f.file);

                        let xhr = new XMLHttpRequest();
                        xhr.open('POST', '{{ route('media.store') }}');
                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                        xhr.upload.onprogress = e => f.progress = Math.round(e.loaded / e.total * 100);

                        xhr.onload = () => {
                            if (xhr.status === 201) {
                                JSON.parse(xhr.responseText).uploaded.forEach(u => this.library.unshift(u));
                            }
                            // remove this file from array
                            this.files.splice(idx, 1);

                            // when all done, reload to ensure fresh thumbnails
                            if (idx === total - 1) {
                                window.location.reload();
                            }
                        };

                        xhr.send(form);
                    });
                },

                deleteMedia(id) {
                    if (!confirm('Delete this media?')) return;
                    fetch(`/media/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(r => r.json())
                        .then(json => {
                            if (json.deleted) this.library = this.library.filter(i => i.id !== id);
                        });
                },

                showModal(item) {
                    this.modalImage = item;
                    this.modalOpen = true;
                }
            }
        }
    </script>
@endpush
