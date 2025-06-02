{{-- resources/views/admin/media/index.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <style>
        /* Custom styles for progress animation and modal */
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }

        .modal-enter-active,
        .modal-leave-active {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .modal-enter-from,
        .modal-leave-to {
            opacity: 0;
            transform: scale(0.95);
        }

        /* Enhanced upload modal styles */
        .upload-modal {
            max-width: 800px;
            /* Spacious width */
            width: 90%;
            max-height: 90vh;
            /* Increased height for better visibility */
            overflow-y: auto;
            background: #ffffff;
            /* Simple white background */
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
            padding: 20px;
            color: #333333;
            /* Dark text for contrast */
        }

        .upload-modal h2 {
            font-size: 1.5rem;
            /* Standard heading size */
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            color: #2a5298;
            /* Blue heading */
        }

        .upload-modal .file-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
            padding: 1rem 0;
            max-height: 400px;
            /* Increased max height */
            overflow-y: auto;
        }

        .upload-modal .file-preview {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .upload-modal .file-preview:hover {
            transform: scale(1.05);
        }

        .upload-modal .file-preview img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .upload-modal .file-preview .progress-bar-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #e5e7eb;
        }

        .upload-modal .file-preview .progress-bar {
            height: 100%;
            background: #4f46e5;
            border-radius: 2px;
        }

        .upload-modal label {
            font-size: 1rem;
            font-weight: 500;
            color: #333333;
            margin-bottom: 8px;
        }

        .upload-modal select,
        .upload-modal input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #ffffff;
            color: #333333;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .upload-modal select:focus,
        .upload-modal input[type="text"]:focus {
            border-color: #4f46e5;
        }

        .upload-modal .flex.gap-2 input[type="text"] {
            flex: 1;
        }

        .upload-modal button {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .upload-modal button.bg-indigo-600 {
            background: #4f46e5;
            color: #ffffff;
        }

        .upload-modal button.bg-indigo-600:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .upload-modal button.bg-gray-200 {
            background: #e5e7eb;
            color: #333333;
        }

        .upload-modal button.bg-gray-200:hover {
            background: #d1d5db;
            transform: translateY(-1px);
        }

        .upload-modal .flex.justify-end.gap-3 {
            position: sticky;
            bottom: 0;
            background: #ffffff;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .upload-modal p {
            color: #dc2626;
            font-size: 0.9rem;
        }

        /* Existing styles for other elements */
        .file-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            padding: 1rem 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .file-preview {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .file-preview img {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }

        .file-preview .progress-bar-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: #e5e7eb;
            overflow: hidden;
        }

        .file-preview .progress-bar {
            height: 100%;
            background-color: #4f46e5;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mediaLibrary', () => ({
                // State
                media: window.initialMedia || [],
                categories: window.initialCategories || [],
                selected: [],
                view: 'grid',
                perPage: 12,
                category: null,
                search: '',
                currentPage: window.initialMeta.current_page || 1,
                lastPage: window.initialMeta.last_page || 1,
                loadError: '',
                isLoading: false,
                modalOpen: false,
                modalImage: {},

                // Upload-modal state
                uploadModalOpen: false,
                uploadErrors: [],
                uploadCategory: null,
                selectedFiles: [],
                newCategoryName: '',
                creatingCategory: false,
                categoryError: '',
                uploading: false,
                uploadProgress: [],

                init() {
                    console.log('MediaLibrary initialized');
                    this.loadMedia(this.currentPage);
                },

                openUpload() {
                    console.log('openUpload called, setting uploadModalOpen to true');
                    this.uploadErrors = [];
                    this.uploadCategory = null;
                    this.selectedFiles = [];
                    this.newCategoryName = '';
                    this.categoryError = '';
                    this.uploadProgress = [];
                    this.uploadModalOpen = true;
                    console.log('uploadModalOpen after set:', this.uploadModalOpen);
                },

                closeUpload() {
                    console.log('closeUpload called, setting uploadModalOpen to false');
                    this.uploadModalOpen = false;
                    this.selectedFiles = [];
                    this.uploadProgress = [];
                },

                handleFileChange(event) {
                    const files = Array.from(event.target.files);
                    this.selectedFiles = files.map(file => ({
                        file,
                        url: URL.createObjectURL(file),
                        selected: true,
                    }));
                    this.uploadProgress = files.map(() => 0);
                },

                toggleFileSelection(index) {
                    this.selectedFiles[index].selected = !this.selectedFiles[index].selected;
                },

                removeFile(index) {
                    URL.revokeObjectURL(this.selectedFiles[index].url);
                    this.selectedFiles.splice(index, 1);
                    this.uploadProgress.splice(index, 1);
                },

                async createCategory() {
                    if (!this.newCategoryName.trim()) {
                        this.categoryError = 'Category name is required.';
                        return;
                    }
                    this.creatingCategory = true;
                    this.categoryError = '';

                    try {
                        const res = await fetch(window.mediaRoutes.categoriesStore, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                name: this.newCategoryName,
                                parent: this.uploadCategory || 0,
                            }),
                        });
                        if (!res.ok) {
                            const json = await res.json();
                            this.categoryError = json.error || 'Failed to create category.';
                        } else {
                            const newCat = await res.json();
                            this.categories.push(newCat);
                            this.uploadCategory = newCat.id;
                            this.newCategoryName = '';
                        }
                    } catch {
                        this.categoryError = 'Network error while creating category.';
                    } finally {
                        this.creatingCategory = false;
                    }
                },

                async upload() {
                    this.uploadErrors = [];
                    this.uploading = true;

                    const selectedFiles = this.selectedFiles.filter(f => f.selected).map(f => f
                        .file);
                    if (!selectedFiles.length) {
                        this.uploadErrors.push('Please select at least one file.');
                        this.uploading = false;
                        return;
                    }
                    if (!this.uploadCategory) {
                        this.uploadErrors.push('Please select or create a category.');
                        this.uploading = false;
                        return;
                    }

                    const form = new FormData();
                    selectedFiles.forEach(f => form.append('files[]', f));
                    form.append('category_id', this.uploadCategory);

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', window.mediaRoutes.store, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector(
                            'meta[name="csrf-token"]').content);

                        xhr.upload.onprogress = (event) => {
                            if (event.lengthComputable) {
                                const totalProgress = (event.loaded / event.total) * 100;
                                this.uploadProgress = this.uploadProgress.map((_, index) => {
                                    const fileProgress = Math.min(totalProgress, 100);
                                    return fileProgress;
                                });
                            }
                        };

                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                this.loadMedia(this.currentPage);
                                this.closeUpload();
                            } else if (xhr.status === 422) {
                                const json = JSON.parse(xhr.responseText);
                                Object.values(json.errors || {}).forEach(arr =>
                                    arr.forEach(msg => this.uploadErrors.push(msg))
                                );
                            } else {
                                this.uploadErrors.push('Upload failed. Please try again.');
                            }
                            this.uploading = false;
                        };

                        xhr.onerror = () => {
                            this.uploadErrors.push('Network error during upload.');
                            this.uploading = false;
                        };

                        xhr.send(form);
                    } catch {
                        this.uploadErrors.push('Network error during upload.');
                        this.uploading = false;
                    }
                },

                async loadMedia(page = 1) {
                    this.isLoading = true;
                    this.loadError = '';
                    try {
                        const params = new URLSearchParams({
                            page: page,
                            per_page: this.perPage,
                            category: this.category || '',
                            search: this.search || '',
                        });
                        const res = await fetch(`${window.mediaRoutes.index}?${params}`, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });
                        const json = await res.json();
                        this.media = json.data;
                        this.currentPage = json.meta.current_page;
                        this.lastPage = json.meta.last_page;
                        this.categories = json.categories;
                    } catch {
                        this.loadError = 'Failed to load media.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                showModal(item) {
                    this.modalImage = item;
                    this.modalOpen = true;
                },

                async deleteMedia(id) {
                    if (!confirm('Delete this media?')) return;
                    await fetch(window.mediaRoutes.destroy.replace('__ID__', id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                        },
                    });
                    this.loadMedia(this.currentPage);
                },
                async bulkDelete() {
                    if (!confirm(`Delete ${this.selected.length} items?`)) return;

                    await fetch(window.mediaRoutes.bulkDelete, {
                        method: 'DELETE', // ← use DELETE
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: this.selected
                        }),
                    });

                    this.selected = [];
                    this.loadMedia(this.currentPage);
                },


                getCategoryNames(item) {
                    const categories = item.categories || [];
                    return Array.isArray(categories) ?
                        categories
                        .map(id => {
                            const cat = this.categories.find(c => c.id === id);
                            return cat ? cat.name : '—';
                        })
                        .join(', ') :
                        '—';
                },
            }));
        });

        // Initial data & routes
        window.initialMedia = @json($initialMedia);
        window.initialMeta = {
            current_page: {{ $mediaPaginator->currentPage() }},
            last_page: {{ $mediaPaginator->lastPage() }},
        };
        window.initialCategories = @json($categories);
        window.mediaRoutes = {
            index: @json(route('admin.media.index')),
            store: @json(route('admin.media.store')),
            destroy: @json(route('admin.media.destroy', ['media' => '__ID__'])),
            bulkDelete: @json(route('admin.media.bulkDelete')),
            categoriesStore: @json(route('admin.media.categories.store')),
        };
    </script>

    <div x-data="mediaLibrary()" x-init="init()" class="min-h-screen p-8 bg-gray-50">
        <div class="bg-white p-6 rounded-xl shadow-sm mb-8 flex flex-wrap gap-4 items-center border border-gray-100">
            <input type="text" x-model.debounce.500ms="search" placeholder="Search media…" @keyup.enter="loadMedia(1)"
                class="flex-1 rounded-lg px-4 py-2 border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm" />
            <select x-model="category" @change="loadMedia(1)"
                class="rounded-lg px-4 py-2 border border-gray-200 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
            <select x-model="perPage" @change="loadMedia(1)"
                class="rounded-lg px-4 py-2 border border-gray-200 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <option value="12">12/page</option>
                <option value="24">24/page</option>
                <option value="48">48/page</option>
                <option value="96">96/page</option>
            </select>
            <button @click="openUpload()"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                + Upload
            </button>
            <button @click="bulkDelete()" :disabled="selected.length === 0"
                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition disabled:opacity-50 shadow-sm">
                Delete (<span x-text="selected.length"></span>)
            </button>
        </div>

        <div x-show="loadError" class="bg-red-50 text-red-700 p-4 rounded-lg mb-8 shadow-sm" x-text="loadError"></div>

        <div x-show="!isLoading" class="space-y-8">
            <div x-show="view==='grid'"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative group rounded-lg overflow-hidden shadow-sm hover:shadow-md transition transform hover:-translate-y-1">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-3 left-3 z-10 h-5 w-5 rounded" />
                        <img :src="item.medium" class="w-full h-auto object-contain" loading="lazy" />
                        <div class="p-3 text-sm truncate bg-gray-50" x-text="item.filename"></div>
                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/60 transition">
                            <button @click="showModal(item)"
                                class="bg-white px-4 py-1 rounded-lg mr-2 hover:bg-gray-100">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-red-600 px-4 py-1 rounded-lg text-white hover:bg-red-700">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="view==='list'" class="space-y-3">
                <template x-for="item in media" :key="item.id">
                    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <div class="flex items-center gap-4">
                            <input type="checkbox" x-model="selected" :value="item.id" class="h-5 w-5 rounded" />
                            <img :src="item.thumbnail" class="w-16 h-16 object-contain rounded-lg" loading="lazy" />
                            <div>
                                <div class="font-semibold text-gray-800" x-text="item.filename"></div>
                                <div class="text-sm text-gray-500" x-text="getCategoryNames(item)"></div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="showModal(item)"
                                class="px-4 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="view==='thumbnail'" class="flex flex-wrap gap-3">
                <template x-for="item in media" :key="item.id">
                    <div class="relative w-28 h-28 rounded-lg overflow-hidden shadow-sm">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-2 left-2 z-10 h-5 w-5 rounded" />
                        <img :src="item.thumbnail" class="w-full h-full object-contain" loading="lazy" />
                    </div>
                </template>
            </div>
        </div>

        <div x-show="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-indigo-500"></div>
            <p class="mt-3 text-gray-600">Loading…</p>
        </div>

        <div x-show="!isLoading && media.length===0 && !loadError" class="text-center py-12">
            <p class="text-gray-600">No media found.</p>
        </div>

        <div x-show="!isLoading && media.length>0 && !loadError" class="mt-6 flex justify-center gap-3">
            <button @click="loadMedia(currentPage-1)" :disabled="currentPage === 1"
                class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-100 disabled:opacity-50 transition">Previous</button>
            <span class="px-4 py-2 text-gray-600">Page <span x-text="currentPage"></span> of <span
                    x-text="lastPage"></span></span>
            <button @click="loadMedia(currentPage+1)" :disabled="currentPage === lastPage"
                class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-100 disabled:opacity-50 transition">Next</button>
        </div>

        <div x-show="modalOpen" x-transition @click.stop="modalOpen=false"
            class="fixed inset-0 bg-black/70 flex items-center justify-center p-6 z-50">
            <div @click.stop class="bg-white rounded-xl shadow-2xl p-6 max-w-4xl w-full">
                <button @click="modalOpen=false"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl">✕</button>
                <img :src="modalImage.original" class="w-full object-contain max-h-[70vh] rounded-lg" />
                <div class="mt-4">
                    <div class="font-semibold text-lg text-gray-800" x-text="modalImage.filename"></div>
                    <div class="text-sm text-gray-500" x-text="getCategoryNames(modalImage)"></div>
                </div>
            </div>
        </div>

        <template x-if="uploadModalOpen">
            <div x-transition x-transition:enter="modal-enter-active" x-transition:leave="modal-leave-active"
                class="fixed inset-0 bg-black/70 flex items-center justify-center p-6 z-50">
                <div class="upload-modal">
                    <h2>Upload Media</h2>

                    <div x-show="uploadErrors.length" class="bg-red-50 text-red-700 p-4 rounded-lg mb-4">
                        <template x-for="err in uploadErrors" :key="err">
                            <p x-text="err" class="text-sm"></p>
                        </template>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label>Select Files</label>
                            <label
                                class="flex items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V4m0 12l-4-4m4 4l4-4m6 4v-6a2 2 0 00-2-2h-6a2 2 0 00-2 2v6m6 0l-4 4m4-4l4 4">
                                        </path>
                                    </svg>
                                    <span class="mt-2 block text-sm text-gray-600"><span
                                            x-text="selectedFiles.length ? selectedFiles.length + ' files selected' : 'No files selected'"></span></span>
                                </div>
                                <input type="file" @change="handleFileChange" multiple class="hidden" />
                            </label>
                            <div class="file-preview-grid mt-2">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="file-preview relative group">
                                        <img :src="file.url" class="w-full h-24 object-contain rounded-lg" />
                                        <input type="checkbox" x-model="file.selected"
                                            class="absolute top-2 left-2 h-5 w-5 rounded" />
                                        <button @click="removeFile(index)"
                                            class="absolute top-2 right-2 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center hover:bg-red-700 opacity-0 group-hover:opacity-100 transition">
                                            ✕
                                        </button>
                                        <div x-show="uploading" class="progress-bar-container">
                                            <div class="progress-bar"
                                                :style="'width:' + (uploadProgress[index] || 0) + '%'"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label>Category</label>
                            <select x-model="uploadCategory"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                <option value="" disabled>Select category</option>
                                <option value="uncategorized">Uncategorized</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label>Or Create New Category</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="newCategoryName" placeholder="New category name"
                                    class="flex-1 border border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 shadow-sm" />
                                <button @click="createCategory()" :disabled="creatingCategory"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                                    <span x-show="!creatingCategory">Create</span>
                                    <span x-show="creatingCategory"
                                        class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-white"></span>
                                </button>
                            </div>
                            <p x-show="categoryError" class="mt-2 text-sm text-red-600" x-text="categoryError"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="closeUpload()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Cancel
                        </button>
                        <button @click="upload()" :disabled="uploading"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
                            <span x-show="!uploading">Upload</span>
                            <span x-show="uploading" class="inline-block animate-pulse">Uploading…</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
