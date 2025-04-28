<div x-data="mediaBrowser()" x-show="isOpen" x-cloak
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div @click.away="close()" class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
        <!-- Header -->
        <header class="flex justify-between items-center p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Media Library</h2>
            <button @click="close()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <!-- Tabs -->
        <div class="px-4 py-2 border-b border-gray-200 flex space-x-4">
            <button
                :class="tab === 'library' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-blue-600'"
                @click="tab = 'library'" class="px-4 py-2 font-medium transition-colors">Library</button>
            <button
                :class="tab === 'upload' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-blue-600'"
                @click="tab = 'upload'" class="px-4 py-2 font-medium transition-colors">Upload</button>
        </div>

        <!-- Body -->
        <div class="p-6 flex-1 overflow-y-auto">
            <!-- LIBRARY TAB -->
            <div x-show="tab === 'library'" class="space-y-6">
                <!-- Filters & Search -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select x-model="filterType" @change="loadLibrary(1)"
                            class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all">All Media</option>
                            <option value="image">Images</option>
                            <option value="video">Videos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <select x-model="filterDate" @change="loadLibrary(1)"
                            class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all">All Dates</option>
                            <template x-for="opt in dateOptions" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" x-model.debounce.500ms="searchTerm" placeholder="Search media..."
                            class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @keyup.enter="loadLibrary(1)" />
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="loadError" class="bg-red-100 text-red-700 p-4 rounded-lg" x-text="loadError"></div>

                <!-- Selected Cart + Insert -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2 overflow-x-auto py-2">
                        <template x-for="img in images.filter(i => selectedIds.includes(i.id))" :key="img.id">
                            <div class="w-16 h-16 relative flex-shrink-0 bg-white border rounded-lg shadow-sm">
                                <img :src="img.url" class="w-full h-full object-contain p-1" />
                                <button @click="toggleSelect(img)"
                                    class="absolute -top-2 -right-2 bg-white rounded-full text-red-600 text-xs p-1 shadow hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="selectedIds.length === 0">
                            <span class="text-gray-400 italic">No images selected</span>
                        </template>
                    </div>
                    <button @click="insertSelected()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 transition-colors"
                        :disabled="selectedIds.length === 0">
                        Insert Selected (<span x-text="selectedIds.length"></span>)
                    </button>
                </div>

                <!-- Loading Spinner -->
                <div x-show="isLoading" class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <!-- Image Grid -->
                <div x-show="!isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="img in images" :key="img.id">
                        <div @click="toggleSelect(img)"
                            class="relative cursor-pointer rounded-lg overflow-hidden bg-white border-2 transition-transform duration-200"
                            :class="selectedIds.includes(img.id) ? 'border-blue-500 scale-105' :
                                'border-gray-200 hover:border-blue-300'">
                            <img :src="img.url" class="w-full h-32 object-cover" loading="lazy" />
                            <template x-if="selectedIds.includes(img.id)">
                                <div
                                    class="absolute inset-0 bg-blue-500 bg-opacity-30 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!isLoading && images.length === 0">
                        <p class="col-span-full text-center text-gray-500 py-8">No media found.</p>
                    </template>
                </div>

                <!-- Pagination -->
                <div x-show="!isLoading && images.length > 0" class="flex justify-center space-x-2 mt-4">
                    <button @click="loadLibrary(currentPage - 1)" :disabled="currentPage === 1"
                        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 hover:bg-gray-300 transition-colors">
                        Previous
                    </button>
                    <span class="px-4 py-2 bg-gray-100 rounded-lg">
                        Page <span x-text="currentPage"></span> of <span x-text="lastPage || 1"></span>
                    </span>
                    <button @click="loadLibrary(currentPage + 1)" :disabled="currentPage === lastPage"
                        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 hover:bg-gray-300 transition-colors">
                        Next
                    </button>
                </div>
            </div>

            <!-- UPLOAD TAB -->
            <div x-show="tab === 'upload'" class="space-y-6">
                <!-- Success Banner -->
                <div x-show="showSuccess" class="p-4 bg-green-100 text-green-800 rounded-lg text-center">
                    Upload complete! Switching to Library...
                </div>

                <!-- Error Message -->
                <div x-show="uploadError" class="bg-red-100 text-red-700 p-4 rounded-lg" x-text="uploadError"></div>

                <!-- Dropzone -->
                <div @dragover.prevent @drop.prevent="handleDrop($event)"
                    class="border-2 border-dashed border-gray-300 p-8 text-center rounded-lg cursor-pointer hover:border-blue-400 transition-colors"
                    @click="$refs.uploadInput.click()">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mt-2 text-gray-600">Click or drag files here to upload</p>
                    <p class="text-sm text-gray-400">Supports multiple files (max 5MB each)</p>
                    <input x-ref="uploadInput" type="file" multiple accept="image/*,video/*" class="hidden"
                        @change="pickFiles($event)" />
                </div>

                <!-- Upload Queue -->
                <div class="space-y-4">
                    <template x-for="(file, idx) in uploads" :key="file.name">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Thumbnail Preview -->
                            <div class="w-16 h-16 border rounded-lg overflow-hidden flex-shrink-0 bg-white">
                                <img :src="file.previewUrl" class="w-full h-full object-contain p-1" />
                            </div>

                            <!-- Name + Progress/Error -->
                            <div class="flex-1 space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium truncate" x-text="file.name"></span>
                                    <template x-if="file.status === 'uploading' && file.lengthComputable">
                                        <span class="text-xs text-gray-500" x-text="file.progress + '%'"></span>
                                    </template>
                                    <template x-if="file.status === 'done'">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="file.status === 'error'">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-blue-600 transition-all duration-300"
                                        :class="!file.lengthComputable && file.status === 'uploading' ? 'animate-pulse w-1/2' :
                                            ''"
                                        :style="file.lengthComputable ? `width: ${file.progress}%;` : ''"></div>
                                </div>
                                <div x-show="file.errorMessages && file.errorMessages.length"
                                    class="text-sm text-red-600 space-y-1">
                                    <template x-for="msg in file.errorMessages" :key="msg">
                                        <p x-text="msg"></p>
                                    </template>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button @click="removeFile(idx)" class="text-red-600 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
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
                loadError: '',
                showSuccess: false,
                uploadError: '',
                images: [],
                uploads: [],
                selectedIds: [],
                filterType: 'all',
                filterDate: 'all',
                searchTerm: '',
                currentPage: 1,
                lastPage: 1,
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
                async loadLibrary(page = 1) {
                    this.isLoading = true;
                    this.loadError = '';
                    try {
                        const params = new URLSearchParams({
                            page,
                            per_page: 20,
                            ...(this.filterType !== 'all' && {
                                type: this.filterType
                            }),
                            ...(this.filterDate !== 'all' && {
                                date: this.filterDate
                            }),
                            ...(this.searchTerm && {
                                search: this.searchTerm
                            }),
                        });

                        const response = await fetch(`{{ route('admin.media.index') }}?${params}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.error || 'Failed to load media');
                        }

                        const {
                            data,
                            meta
                        } = await response.json();
                        this.images = data || [];
                        this.currentPage = meta.current_page;
                        this.lastPage = meta.last_page;
                    } catch (error) {
                        console.error('Error loading media:', error);
                        this.loadError = error.message || 'Failed to load media. Please try again.';
                    } finally {
                        this.isLoading = false;
                    }
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
                    const months = Array.from(new Set(this.images.map(i => i.created_at?.slice(0,
                        7) || '')));
                    return months.filter(m => m).sort().map(m => ({
                        value: m,
                        label: new Date(m + '-01').toLocaleString('default', {
                            month: 'long',
                            year: 'numeric'
                        })
                    }));
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
                    this.uploadError = '';
                    Array.from(files).forEach(file => {
                        if (!file.type.match(/^(image|video)\//)) {
                            this.uploadError = 'Only image and video files are supported';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) { // 5MB limit
                            this.uploadError = 'File size must be less than 5MB';
                            return;
                        }
                        const entry = {
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

                async uploadFile(entry) {
                    entry.status = 'uploading';
                    entry.lengthComputable = false;
                    entry.errorMessages = [];

                    const form = new FormData();
                    form.append('files[]', entry.file); // Match MediaController@store expectation

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', "{{ route('admin.media.store') }}", true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector(
                            'meta[name=csrf-token]').content);
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.onprogress = e => {
                            if (e.lengthComputable) {
                                entry.lengthComputable = true;
                                entry.progress = Math.round((e.loaded / e.total) * 100);
                            }
                        };

                        xhr.onload = () => {
                            if (xhr.status === 201) {
                                const json = JSON.parse(xhr.responseText);
                                const uploaded = json.uploaded || [];
                                uploaded.forEach(item => {
                                    this.images.unshift(item);
                                    this.selectedIds.push(item.id);
                                });
                                entry.status = 'done';
                                entry.progress = 100;
                            } else {
                                let err;
                                try {
                                    err = JSON.parse(xhr.responseText);
                                } catch {
                                    err = {
                                        error: 'Unknown error occurred'
                                    };
                                }
                                entry.errorMessages = err.error ? [err.error] : [
                                    'Failed to upload file'
                                ];
                                entry.status = 'error';
                            }
                            this.checkAllDone();
                        };

                        xhr.onerror = () => {
                            entry.status = 'error';
                            entry.errorMessages = ['Network error occurred'];
                            this.checkAllDone();
                        };

                        xhr.send(form);
                    } catch (error) {
                        console.error('Upload error:', error);
                        entry.status = 'error';
                        entry.errorMessages = ['Failed to upload file'];
                        this.checkAllDone();
                    }
                },

                removeFile(idx) {
                    this.uploads.splice(idx, 1);
                },

                checkAllDone() {
                    if (this.uploads.every(u => u.status !== 'uploading')) {
                        this.showSuccess = true;
                        setTimeout(() => {
                            this.showSuccess = false;
                            this.tab = 'library';
                            this.uploads = [];
                            this.loadLibrary(this.currentPage); // Refresh library
                        }, 2000);
                    }
                },

                // Close & Reset
                close() {
                    this.isOpen = false;
                    this.tab = 'library';
                    this.isLoading = false;
                    this.loadError = '';
                    this.showSuccess = false;
                    this.uploadError = '';
                    this.images = [];
                    this.uploads = [];
                    this.selectedIds = [];
                    this.filterType = 'all';
                    this.filterDate = 'all';
                    this.searchTerm = '';
                    this.currentPage = 1;
                    this.lastPage = 1;
                    this.callback = null;
                }
            }));
        });
    </script>
@endpush
