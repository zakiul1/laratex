@extends('layouts.dashboard')

@section('content')
    <div x-data="mediaLibrary()" x-init="init()" class="p-6 bg-gray-100 min-h-screen">
        <!-- Toolbar -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1">
                    <input type="text" x-model.debounce.500ms="search" placeholder="Search media..."
                        class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        @keyup.enter="loadMedia(1)" />
                </div>

                <select x-model="category" @change="loadMedia(1)"
                    class="border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name || 'Unnamed Category'"></option>
                    </template>
                </select>

                <select x-model="perPage" @change="loadMedia(1)"
                    class="border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    <option value="12">12 / page</option>
                    <option value="24">24 / page</option>
                    <option value="48">48 / page</option>
                    <option value="96">96 / page</option>
                </select>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="loadError" class="bg-red-100 text-red-700 p-4 rounded-lg mb-6" x-text="loadError"></div>

        <!-- View Toggles & Actions -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex space-x-4">
                <button @click="view = 'grid'"
                    :class="view === 'grid' ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:text-purple-600'"
                    class="px-4 py-2 rounded-lg transition-colors">Grid</button>
                <button @click="view = 'list'"
                    :class="view === 'list' ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:text-purple-600'"
                    class="px-4 py-2 rounded-lg transition-colors">List</button>
                <button @click="view = 'thumb'"
                    :class="view === 'thumb' ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:text-purple-600'"
                    class="px-4 py-2 rounded-lg transition-colors">Thumbnail</button>
            </div>

            <div class="flex gap-2">
                <button @click="openUpload()"
                    class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload
                </button>

                <button @click="bulkDelete()" :disabled="selected.length === 0"
                    class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg disabled:opacity-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete (<span x-text="selected.length"></span>)
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-purple-500"></div>
            <p class="mt-2 text-gray-600">Loading media...</p>
        </div>

        <!-- Media Views -->
        <div x-show="!isLoading" class="space-y-6">
            <!-- Grid View -->
            <div x-show="view === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative group bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-2 left-2 z-10 rounded border-gray-300 bg-white" />

                        <img :src="item.url" class="w-full h-40 object-cover" loading="lazy" />

                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/50 transition-opacity">
                            <button @click="showModal(item)"
                                class="bg-white p-2 rounded-full shadow mr-2 hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-red-500 p-2 rounded-full shadow text-white hover:bg-red-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-2 text-sm text-center truncate" x-text="item.filename"></div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="view === 'list'" class="space-y-2">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="flex items-center justify-between bg-white rounded-lg shadow p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <input type="checkbox" x-model="selected" :value="item.id"
                                class="rounded border-gray-300" />
                            <img :src="item.url" class="w-12 h-12 object-cover rounded" loading="lazy" />
                            <div>
                                <div class="font-medium" x-text="item.filename"></div>
                                <div class="text-sm text-gray-500" x-text="getCategoryNames(item)"></div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="showModal(item)" class="text-purple-600 hover:text-purple-700">View</button>
                            <button @click="deleteMedia(item.id)" class="text-red-600 hover:text-red-700">Delete</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Thumbnail View -->
            <div x-show="view === 'thumb'" class="flex flex-wrap gap-2">
                <template x-for="item in media" :key="item.id">
                    <div class="relative w-24 h-24 bg-white rounded-lg shadow overflow-hidden">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-1 left-1 z-10 rounded border-gray-300 bg-white" />
                        <img :src="item.url" class="w-full h-full object-cover" loading="lazy" />
                    </div>
                </template>
            </div>
        </div>

        <!-- No Results -->
        <div x-show="!isLoading && media.length === 0 && !loadError" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-2 text-gray-600">No media found. Try adjusting your search or upload new media.</p>
        </div>

        <!-- Pagination -->
        <div x-show="!isLoading && media.length > 0 && !loadError" class="mt-6 flex justify-center">
            <div class="flex gap-2">
                <button @click="loadMedia(currentPage - 1)" :disabled="currentPage === 1"
                    class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 hover:bg-gray-300 transition-colors">
                    Previous
                </button>
                <span class="px-4 py-2 bg-gray-100 rounded-lg">
                    Page <span x-text="currentPage"></span> of <span x-text="lastPage || 1"></span>
                </span>
                <button @click="loadMedia(currentPage + 1)" :disabled="currentPage === lastPage"
                    class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 hover:bg-gray-300 transition-colors">
                    Next
                </button>
            </div>
        </div>

        <!-- Preview Modal -->
        <div x-show="modalOpen" x-transition class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
            @click="modalOpen = false">
            <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full relative overflow-hidden">
                <button @click="modalOpen = false"
                    class="absolute top-4 right-4 bg-white rounded-full p-2 shadow hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="modalImage.url" class="w-full max-h-[80vh] object-contain" />
                <div class="p-4 border-t">
                    <div class="font-medium" x-text="modalImage.filename"></div>
                    <div class="text-sm text-gray-500" x-text="getCategoryNames(modalImage)"></div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div x-show="uploadModalOpen" x-transition
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div @click.away="closeUpload()" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-semibold">Upload Media</h3>
                    <button @click="closeUpload()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Category Selector -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <div class="flex gap-4">
                        <select x-model="selectedCategory" x-ref="categorySelect"
                            class="flex-1 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">Uncategorized</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name || 'Unnamed Category'"></option>
                            </template>
                        </select>
                        <button @click="showAddCategory = !showAddCategory"
                            class="px-4 py-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- New Category Form -->
                <div x-show="showAddCategory" x-transition class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">New Category</label>
                    <div class="flex gap-4">
                        <input x-model="newCategoryName" type="text" placeholder="Category name"
                            class="flex-1 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500" />
                        <button @click="addCategory()"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Create
                        </button>
                    </div>
                </div>

                <!-- Drag & Drop Zone -->
                <div @drop.prevent="addFiles($event)" @dragover.prevent
                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-purple-400 transition-colors">
                    <input x-ref="fileInput" type="file" multiple accept="image/*" @change="addFiles"
                        class="hidden" />
                    <div x-show="files.length === 0">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-2">
                            Drag & drop images here, or
                            <button @click="$refs.fileInput.click()"
                                class="text-purple-600 hover:text-purple-700">browse</button>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, PNG, GIF</p>
                    </div>
                    <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <template x-for="(file, idx) in files" :key="idx">
                            <div class="relative rounded-lg overflow-hidden bg-gray-100">
                                <img :src="file.preview" class="w-full h-24 object-cover" />
                                <button @click="removeFile(idx)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-200">
                                    <div class="h-full bg-purple-600 transition-all" :style="`width: ${file.progress}%`">
                                    </div>
                                </div>
                                <div class="text-xs p-1 truncate bg-white/90" x-text="file.file.name"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Upload Actions -->
                <div class="flex justify-between items-center">
                    <div x-show="uploadError" class="text-red-600 text-sm" x-text="uploadError"></div>
                    <div class="flex gap-4 ml-auto">
                        <button @click="closeUpload()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button @click="uploadFiles()" :disabled="files.length === 0 || isUploading"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 flex items-center gap-2">
                            <span x-show="isUploading"
                                class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Upload (<span x-text="files.length"></span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Debug the categories data
        console.log('Initial Categories:', @json($categories));
        window.initialCategories = @json($categories);

        document.addEventListener('alpine:init', () => {
            Alpine.data('mediaLibrary', () => ({
                // State
                media: [],
                categories: window.initialCategories || [],
                view: 'grid',
                selected: [],
                search: '',
                category: '',
                perPage: 12,
                currentPage: 1,
                lastPage: 1,
                modalOpen: false,
                modalImage: {},
                uploadModalOpen: false,
                files: [],
                selectedCategory: '',
                showAddCategory: false,
                newCategoryName: '',
                isLoading: false,
                isUploading: false,
                uploadError: '',
                loadError: '',

                // Initialization
                init() {
                    const url = new URL(window.location);
                    this.search = url.searchParams.get('search') || '';
                    this.category = url.searchParams.get('category') || '';
                    this.perPage = url.searchParams.get('per_page') || 12;
                    this.loadMedia();
                    this.fetchCategories(); // Ensure categories are loaded on init

                    this.$watch('search', () => this.loadMedia());
                    this.$watch('category', () => this.loadMedia());
                    this.$watch('perPage', () => this.loadMedia());
                },

                // Helper to get category names for display
                getCategoryNames(item) {
                    if (!item.categories || item.categories.length === 0) {
                        return 'Uncategorized';
                    }
                    const names = this.categories
                        .filter(cat => item.categories.includes(cat.id))
                        .map(cat => cat.name || 'Unnamed Category');
                    return names.length > 0 ? names.join(', ') : 'Uncategorized';
                },

                // Fetch categories from the server
                async fetchCategories() {
                    try {
                        const response = await fetch('{{ route('admin.media.index') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        if (!response.ok) throw new Error('Failed to fetch categories');
                        const data = await response.json();
                        this.categories = data.categories || [];
                        // Ensure categories are unique by id
                        const seenIds = new Set();
                        this.categories = this.categories.filter(cat => {
                            if (!cat || !cat.id || seenIds.has(cat.id)) {
                                return false;
                            }
                            seenIds.add(cat.id);
                            return true;
                        });
                    } catch (error) {
                        console.error('Error fetching categories:', error);
                        this.uploadError = 'Failed to fetch categories. Please try again.';
                    }
                },

                // Media Loading
                async loadMedia(page = 1) {
                    this.isLoading = true;
                    this.loadError = ''; // Reset error message
                    try {
                        const params = new URLSearchParams({
                            page,
                            per_page: this.perPage,
                            ...(this.search && {
                                search: this.search
                            }),
                            ...(this.category && {
                                category: this.category
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
                        this.media = data || [];
                        this.currentPage = meta.current_page;
                        this.lastPage = meta.last_page;

                        const newUrl = `${window.location.pathname}?${params}`;
                        window.history.pushState({}, '', newUrl);
                    } catch (error) {
                        console.error('Error loading media:', error);
                        this.loadError = error.message || 'Failed to load media. Please try again.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Media Actions
                showModal(item) {
                    this.modalImage = item;
                    this.modalOpen = true;
                },

                async deleteMedia(id) {
                    if (!confirm('Are you sure you want to delete this media?')) return;
                    try {
                        const response = await fetch(`{{ url('admin/media') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (!response.ok) throw new Error('Failed to delete media');
                        await this.loadMedia();
                    } catch (error) {
                        console.error('Error deleting media:', error);
                        this.loadError = 'Failed to delete media. Please try again.';
                    }
                },

                async bulkDelete() {
                    if (!confirm(`Are you sure you want to delete ${this.selected.length} items?`))
                        return;
                    try {
                        const response = await fetch('{{ route('admin.media.bulkDelete') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: this.selected
                            })
                        });

                        if (!response.ok) throw new Error('Failed to delete media');
                        this.selected = [];
                        await this.loadMedia();
                    } catch (error) {
                        console.error('Error bulk deleting:', error);
                        this.loadError = 'Failed to delete media. Please try again.';
                    }
                },

                // Upload Handling
                openUpload() {
                    this.files = [];
                    this.uploadError = '';
                    this.uploadModalOpen = true;
                    this.fetchCategories(); // Refresh categories when opening the upload modal
                },

                closeUpload() {
                    this.uploadModalOpen = false;
                    this.showAddCategory = false;
                    this.newCategoryName = '';
                    this.files = [];
                    this.isUploading = false;
                    this.uploadError = '';
                },

                addFiles(event) {
                    this.uploadError = '';
                    const files = event.dataTransfer?.files || event.target.files;
                    Array.from(files).forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            this.uploadError = 'Only image files are supported';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) { // 5MB limit to match controller
                            this.uploadError = 'File size must be less than 5MB';
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.files.push({
                                file,
                                preview: e.target.result,
                                progress: 0,
                                error: null
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                    if (event.target) event.target.value = '';
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                async addCategory() {
                    if (!this.newCategoryName.trim()) {
                        this.uploadError = 'Category name is required';
                        return;
                    }
                    try {
                        const response = await fetch(
                        '{{ route('admin.media.categories.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.newCategoryName,
                                parent: 0
                            })
                        });

                        const data = await response.json();
                        if (!response.ok || data.error) {
                            throw new Error(data.error || 'Failed to create category');
                        }

                        // Refresh categories from the server and select the new category
                        await this.fetchCategories();
                        this.selectedCategory = data.id
                    .toString(); // Ensure the ID is a string to match the select value
                        this.showAddCategory = false;
                        this.newCategoryName = '';
                    } catch (error) {
                        console.error('Error creating category:', error);
                        this.uploadError = error.message ||
                            'Failed to create category. Please try again.';
                    }
                },

                async uploadFiles() {
                    if (this.files.length === 0) return;
                    this.isUploading = true;
                    this.uploadError = '';

                    try {
                        const formData = new FormData();
                        this.files.forEach(fileObj => {
                            formData.append('files[]', fileObj.file);
                        });
                        if (this.selectedCategory) {
                            formData.append('category_id', this.selectedCategory);
                        }

                        const response = await fetch('{{ route('admin.media.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (!response.ok || data.error) {
                            throw new Error(data.error || 'Failed to upload files');
                        }

                        await this.loadMedia();
                        this.closeUpload();
                    } catch (error) {
                        console.error('Error uploading files:', error);
                        this.uploadError = error.message ||
                            'Failed to upload files. Please try again.';
                    } finally {
                        this.isUploading = false;
                    }
                }
            }));
        });
    </script>
@endpush
