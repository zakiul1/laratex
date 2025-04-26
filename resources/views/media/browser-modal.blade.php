{{-- resources/views/media/browser-modal.blade.php --}}
<div x-data="mediaBrowser()" x-show="isOpen" x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div @click.away="close()" class="bg-white rounded-lg overflow-hidden shadow-xl w-11/12 max-w-4xl">
        {{-- Header --}}
        <header class="flex justify-between items-center p-4 border-b">
            <h2 class="font-semibold">Media Library</h2>
            <button @click="close()" class="text-gray-600 hover:text-gray-900">✕</button>
        </header>

        {{-- Tabs --}}
        <div class="px-4 py-2 border-b flex space-x-4">
            <button :class="tab === 'library' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                @click="tab = 'library'" class="px-3 py-1">Library</button>
            <button :class="tab === 'upload' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                @click="tab = 'upload'" class="px-3 py-1">Upload</button>
        </div>

        <div class="p-4 h-96 overflow-auto">
            {{-- ── LIBRARY ─────────────────────────────────── --}}
            <div x-show="tab === 'library'" class="space-y-4">
                {{-- Filters & Search --}}
                <div class="flex items-end space-x-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Type</label>
                        <select x-model="filterType" class="border rounded px-2 py-1 text-sm">
                            <option value="all">All media</option>
                            <option value="image">Images</option>
                            <option value="video">Videos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Date</label>
                        <select x-model="filterDate" class="border rounded px-2 py-1 text-sm">
                            <option value="all">All dates</option>
                            <template x-for="opt in dateOptions" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Search</label>
                        <input type="text" x-model="searchTerm" placeholder="Search media…"
                            class="w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- Selected Cart + Insert --}}
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2 overflow-x-auto py-1">
                        <template x-for="img in images.filter(i => selectedIds.includes(i.id))" :key="img.id">
                            <div class="w-16 h-16 relative flex-shrink-0 bg-white border rounded">
                                <img :src="img.url" class="w-full h-full object-contain p-1" />
                                <button @click="toggleSelect(img)"
                                    class="absolute -top-2 -right-2 bg-white rounded-full text-red-600 text-xs p-0.5">×</button>
                            </div>
                        </template>
                        <template x-if="selectedIds.length === 0">
                            <span class="text-gray-400 italic">No images selected</span>
                        </template>
                    </div>
                    <button @click="insertSelected()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded"
                        :disabled="selectedIds.length === 0">
                        Insert Selected (<span x-text="selectedIds.length"></span>)
                    </button>
                </div>

                {{-- Loading Spinner --}}
                <template x-if="isLoading">
                    <div class="flex justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                    </div>
                </template>

                {{-- Image Grid --}}
                <div x-show="!isLoading" class="grid grid-cols-5 gap-2">
                    <template x-for="img in filteredImages" :key="img.id">
                        <div @click="toggleSelect(img)"
                            class="relative w-full h-full cursor-pointer rounded  overflow-hidden bg-white"
                            :class="selectedIds.includes(img.id) ? 'border-blue-500' : 'border-transparent'">
                            <img :src="img.url" class="w-full h-auto object-cover  border" />
                            <template x-if="selectedIds.includes(img.id)">
                                <div
                                    class="absolute inset-0 bg-blue-500 bg-opacity-30 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!isLoading && filteredImages.length === 0">
                        <p class="col-span-4 text-center text-gray-500">No media found.</p>
                    </template>
                </div>
            </div>

            {{-- ── UPLOAD ─────────────────────────────────── --}}
            <div x-show="tab === 'upload'" class="space-y-4">
                {{-- Success Banner --}}
                <template x-if="showSuccess">
                    <div class="p-2 bg-green-100 text-green-800 rounded text-center">Upload complete!</div>
                </template>

                {{-- Dropzone --}}
                <div @dragover.prevent @drop.prevent="handleDrop($event)"
                    class="border-2 border-dashed border-gray-300 p-6 text-center rounded cursor-pointer"
                    @click="$refs.uploadInput.click()">
                    <p class="mb-2 text-gray-600">Click or drag files here to upload</p>
                    <p class="text-sm text-gray-400">Supports multiple files</p>
                    <input x-ref="uploadInput" type="file" multiple class="hidden" @change="pickFiles($event)" />
                </div>

                {{-- Upload Queue --}}
                <template x-for="file in uploads" :key="file.name">
                    <div class="flex items-center space-x-3">
                        {{-- Thumbnail Preview --}}
                        <div class="w-16 h-16 border rounded overflow-hidden flex-shrink-0 bg-white">
                            <img :src="file.previewUrl" class="w-full h-full object-contain p-1" />
                        </div>

                        {{-- Name + Progress/Error --}}
                        <div class="flex-1 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium" x-text="file.name"></span>
                                <template x-if="file.status === 'uploading' && file.lengthComputable">
                                    <span class="text-xs text-gray-500" x-text="file.progress + '%'"></span>
                                </template>
                                <template x-if="file.status === 'done'">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </template>
                            </div>
                            <div class="w-full bg-gray-200 rounded h-2 overflow-hidden">
                                <div class="h-full bg-blue-600 transition-all"
                                    :class="!file.lengthComputable && file.status==='uploading' ? 'animate-pulse w-1/2' : ''"
                                    :style="file.lengthComputable ? `width: ${file.progress}%;` : ''"></div>
                            </div>
                            <template x-if="file.errorMessages && file.errorMessages.length">
                                <div class="text-sm text-red-600 space-y-1">
                                    <template x-for="msg in file.errorMessages" :key="msg">
                                        <p x-text="msg"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mediaBrowser', () => ({
                // State
                isOpen: false,
                tab: 'library',
                isLoading: false,
                showSuccess: false,
                images: [],
                uploads: [],
                selectedIds: [],
                filterType: 'all',
                filterDate: 'all',
                searchTerm: '',
                callback: null,

                // Init
                init() {
                    document.addEventListener('media-open', e => {
                        this.callback = e.detail.onSelect;
                        this.loadLibrary();
                        this.isOpen = true;
                    });
                },

                // Library
                loadLibrary() {
                    this.isLoading = true;
                    fetch("{{ route('media.json') }}", {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => this.images = data)
                        .catch(err => console.error(err))
                        .finally(() => this.isLoading = false);
                },
                toggleSelect(img) {
                    this.selectedIds = this.selectedIds.includes(img.id) ?
                        this.selectedIds.filter(i => i !== img.id) : [...this.selectedIds, img.id];
                },
                insertSelected() {
                    this.images
                        .filter(i => this.selectedIds.includes(i.id))
                        .forEach(i => this.callback(i));
                    this.close();
                },

                // Filtering
                get dateOptions() {
                    let months = Array.from(new Set(this.images.map(i => i.uploaded_at?.slice(0,
                        7) || '')));
                    return months.filter(m => m).sort().map(m => ({
                        value: m,
                        label: new Date(m + '-01')
                            .toLocaleString('default', {
                                month: 'long',
                                year: 'numeric'
                            })
                    }));
                },
                get filteredImages() {
                    return this.images.filter(img => {
                        if (this.filterType !== 'all' && !img.mime_type?.startsWith(this
                                .filterType)) return false;
                        if (this.filterDate !== 'all' && img.uploaded_at?.slice(0, 7) !==
                            this.filterDate) return false;
                        if (this.searchTerm && !img.url.toLowerCase().includes(this
                                .searchTerm.toLowerCase())) return false;
                        return true;
                    });
                },

                // Upload
                handleDrop(e) {
                    this.uploadFiles(e.dataTransfer.files);
                },
                pickFiles(e) {
                    this.uploadFiles(e.target.files);
                    e.target.value = '';
                },
                uploadFiles(files) {
                    Array.from(files).forEach(file => {
                        let entry = {
                            file,
                            name: file.name,
                            progress: 0,
                            status: 'queued',
                            previewUrl: URL.createObjectURL(file),
                            lengthComputable: false,
                            errorMessages: []
                        };
                        this.uploads.push(entry);
                        this.uploadFile(entry);
                    });
                },
                uploadFile(entry) {
                    entry.status = 'uploading';
                    entry.lengthComputable = false;
                    entry.errorMessages = [];

                    let xhr = new XMLHttpRequest(),
                        form = new FormData();
                    form.append('file', entry.file);

                    xhr.open('POST', "{{ route('media.store') }}", true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]')
                        .content);
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.onprogress = e => {
                        if (e.lengthComputable) {
                            entry.lengthComputable = true;
                            entry.progress = Math.round((e.loaded / e.total) * 100);
                        }
                    };
                    xhr.onload = () => {
                        if (xhr.status === 200) {
                            let json;
                            try {
                                json = JSON.parse(xhr.responseText)
                            } catch {}
                            if (json?.id && json?.url) {
                                this.images.unshift(json);
                                this.selectedIds.push(json.id);
                            }
                            entry.status = 'done';
                            entry.progress = 100;
                        } else if (xhr.status === 422) {
                            let err;
                            try {
                                err = JSON.parse(xhr.responseText)
                            } catch {}
                            entry.errorMessages = err?.errors?.file || [];
                            entry.status = 'error';
                        } else {
                            entry.status = 'error';
                        }
                        this.checkAllDone();
                    };
                    xhr.onerror = () => {
                        entry.status = 'error';
                        this.checkAllDone();
                    };
                    xhr.send(form);
                },
                checkAllDone() {
                    if (this.uploads.every(u => u.status !== 'uploading')) {
                        this.showSuccess = true;
                        setTimeout(() => {
                            this.showSuccess = false;
                            this.tab = 'library';
                            this.uploads = [];
                        }, 2000);
                    }
                },

                // Close & reset
                close() {
                    this.isOpen = false;
                    this.tab = 'library';
                    this.isLoading = false;
                    this.showSuccess = false;
                    this.images = [];
                    this.uploads = [];
                    this.selectedIds = [];
                    this.callback = null;
                    this.filterType = 'all';
                    this.filterDate = 'all';
                    this.searchTerm = '';
                }
            }));
        });
    </script>
@endpush
