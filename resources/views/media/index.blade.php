@extends('layouts.dashboard')

@section('content')
    {{-- Seed initial data so Alpine can render the first page immediately --}}
    <script>
        window.initialMedia = @json($initialMedia);
        window.initialMeta = {
            current_page: {{ $mediaPaginator->currentPage() }},
            last_page: {{ $mediaPaginator->lastPage() }}
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

    <div x-data="mediaLibrary()" x-init="init()" class="  dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <!-- Toolbar -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl mb-8">
            <div class="flex flex-wrap gap-6 items-center">
                <div class="flex-1">
                    <input type="text" x-model.debounce.500ms="search" placeholder="Search media..."
                        class="w-full border-gray-200 dark:border-gray-600 rounded-xl px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 shadow-sm hover:shadow-md"
                        @keyup.enter="loadMedia(1)" />
                </div>

                <select x-model="category" @change="loadMedia(1)"
                    class="border-gray-200 dark:border-gray-600 rounded-xl px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 transition-all duration-300 shadow-sm hover:shadow-md">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name || 'Unnamed Category'"></option>
                    </template>
                </select>

                <select x-model="perPage" @change="loadMedia(1)"
                    class="border-gray-200 dark:border-gray-600 rounded-xl px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 transition-all duration-300 shadow-sm hover:shadow-md">
                    <option value="12">12 / page</option>
                    <option value="24">24 / page</option>
                    <option value="48">48 / page</option>
                    <option value="96">96 / page</option>
                </select>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="loadError"
            class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 p-4 rounded-2xl mb-8 shadow-md"
            x-text="loadError"></div>

        <!-- View Toggles & Actions -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex space-x-4">
                <button @click="view = 'grid'"
                    :class="view === 'grid' ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400'"
                    class="px-5 py-2 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg">Grid</button>
                <button @click="view = 'list'"
                    :class="view === 'list' ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400'"
                    class="px-5 py-2 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg">List</button>
                <button @click="view = 'thumbnail'"
                    :class="view === 'thumbnail' ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400'"
                    class="px-5 py-2 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg">thumbnailnail</button>
            </div>

            <div class="flex gap-3">
                <button @click="openUpload()"
                    class="flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-5 py-2 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-xl">+
                    Upload</button>

                <button @click="bulkDelete()" :disabled="selected.length === 0"
                    class="flex items-center gap-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-5 py-2 rounded-xl font-semibold disabled:opacity-50 transition-all duration-300 shadow-md hover:shadow-xl">Delete
                    (<span x-text="selected.length"></span>)</button>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-purple-500"></div>
            <p class="mt-3 text-gray-600 dark:text-gray-300 font-medium">Loading media...</p>
        </div>

        <!-- Media Views -->
        <div x-show="!isLoading" class="space-y-8">
            <!-- Grid View -->
            <div x-show="view === 'grid'"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative group bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-3 left-3 z-10 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm" />

                        <img :src="item.url"
                            class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 rounded-t-xl"
                            loading="lazy" />

                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-gradient-to-t from-black/70 to-transparent transition-opacity duration-300">
                            <button @click="showModal(item)"
                                class="bg-white dark:bg-gray-700 p-3 rounded-full shadow-lg mr-3 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium transition-all duration-200">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-red-600 p-3 rounded-full shadow-lg text-white hover:bg-red-700 font-medium transition-all duration-200">Delete</button>
                        </div>

                        <div class="p-3 text-sm text-center font-semibold text-gray-800 dark:text-gray-200 truncate"
                            x-text="item.filename"></div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="view === 'list'" class="space-y-3">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl shadow-md p-5 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-300">
                        <div class="flex items-center gap-5">
                            <input type="checkbox" x-model="selected" :value="item.id"
                                class="rounded border-gray-300 dark:border-gray-600 shadow-sm" />
                            <img :src="item.url"
                                class="w-16 h-16 object-cover rounded-lg transition-transform duration-300 hover:scale-105"
                                loading="lazy" />
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="item.filename"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="getCategoryNames(item)"></div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button @click="showModal(item)"
                                class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-2 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 transition-all duration-200 shadow-sm hover:shadow-md">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-lg font-medium hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-sm hover:shadow-md">Delete</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- thumbnailnail View -->
            <div x-show="view === 'thumbnail'" class="flex flex-wrap gap-3">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative w-28 h-28 bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-2 left-2 z-10 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm" />
                        <img :src="item.url"
                            class="w-full h-full object-cover transition-transform duration-300 rounded-lg"
                            loading="lazy" />
                    </div>
                </template>
            </div>
        </div>

        <!-- No Results -->
        <div x-show="!isLoading && media.length === 0 && !loadError" class="text-center py-12">
            <p class="mt-3 text-gray-600 dark:text-gray-300 font-medium">No media found. Try adjusting your search or upload
                new media.</p>
        </div>

        <!-- Pagination -->
        <div x-show="!isLoading && media.length > 0 && !loadError" class="mt-8 flex justify-center gap-3">
            <button @click="loadMedia(currentPage - 1)" :disabled="currentPage === 1"
                class="px-5 py-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-semibold disabled:opacity-50 hover:from-gray-300 hover:to-gray-400 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-300 shadow-md">Previous</button>
            <span
                class="px-5 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl font-semibold shadow-md">
                Page <span x-text="currentPage"></span> of <span x-text="lastPage || 1"></span>
            </span>
            <button @click="loadMedia(currentPage + 1)" :disabled="currentPage === lastPage"
                class="px-5 py-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-semibold disabled:opacity-50 hover:from-gray-300 hover:to-gray-400 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-300 shadow-md">Next</button>
        </div>

        <!-- Preview Modal -->
        <div x-show="modalOpen" x-transition class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-6"
            @click="modalOpen = false">
            <div @click.stop
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-4xl w-full relative overflow-hidden">
                <button @click="modalOpen = false"
                    class="absolute top-4 right-4 bg-white dark:bg-gray-700 rounded-full p-2 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-all duration-200">✕</button>
                <img :src="modalImage.url" class="w-full max-h-[80vh] object-contain rounded-t-2xl" />
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="modalImage.filename"></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="getCategoryNames(modalImage)"></div>
                </div>
            </div>
        </div>
        <!-- Upload Modal -->
        <div x-show="uploadModalOpen" x-transition
            class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-6">
            <div @click.away="closeUpload()"
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl
           p-8 flex flex-col space-y-6
           max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        Upload Media
                    </h3>
                    <button @click="closeUpload()"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all duration-200">
                        ✕
                    </button>
                </div>

                <!-- Category Selector -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category
                    </label>
                    <div class="flex gap-4">
                        <select x-model="selectedCategory" x-ref="categorySelect"
                            class="flex-1 border-gray-200 dark:border-gray-600 rounded-xl px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 transition-all duration-300 shadow-sm hover:shadow-md">
                            <option value="">— None —</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name || 'Unnamed Category'"></option>
                            </template>
                        </select>
                        <button @click="showAddCategory = !showAddCategory"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-md hover:shadow-lg">+</button>
                    </div>
                </div>

                <!-- New Category Form -->
                <div x-show="showAddCategory" x-transition class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        New Category
                    </label>
                    <div class="flex gap-4">
                        <input x-model="newCategoryName" type="text" placeholder="Category name"
                            class="flex-1 border-gray-200 dark:border-gray-600 rounded-xl px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 transition-all duration-300 shadow-sm hover:shadow-md" />
                        <button @click="addCategory()"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-md hover:shadow-lg">Create</button>
                    </div>
                </div>

                <!-- Drag & Drop Zone -->
                <div @click="$refs.fileInput.click()" @drop.prevent="addFiles($event)" @dragover.prevent
                    class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-6 text-center cursor-pointer hover:border-purple-400 dark:hover:border-purple-600 transition-all duration-300">
                    <input x-ref="fileInput" type="file" multiple accept="image/*" class="hidden"
                        @change="addFiles" />

                    <div x-show="files.length === 0">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903..." />
                        </svg>
                        <p class="mt-2">
                            Drag & drop images here, or
                            <button type="button" @click.prevent="$refs.fileInput.click()"
                                class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-500 underline transition-all duration-200">
                                browse
                            </button>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            JPG, PNG, GIF only; max 5MB
                        </p>
                    </div>

                    <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <template x-for="(file, idx) in files" :key="idx">
                            <div class="relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-md">
                                <img :src="file.preview"
                                    class="w-full h-24 object-cover transition-transform duration-300 hover:scale-105" />
                                <button @click="removeFile(idx)"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-all duration-200 shadow-sm">×</button>
                                <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-200 dark:bg-gray-600">
                                    <div class="h-full bg-purple-600 transition-all" :style="`width: ${file.progress}%`">
                                    </div>
                                </div>
                                <div class="text-xs p-1 truncate bg-white/90 dark:bg-gray-800/90 text-gray-800 dark:text-gray-200"
                                    x-text="file.file.name"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Upload Actions (stick to bottom) -->
                <div class="mt-auto flex justify-between items-center">
                    <div x-show="uploadError" class="text-red-600 dark:text-red-400 text-sm" x-text="uploadError"></div>
                    <div class="flex gap-4 ml-auto">
                        <button @click="closeUpload()"
                            class="px-5 py-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 rounded-xl hover:from-gray-300 hover:to-gray-400 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-300 shadow-md">Cancel</button>
                        <button @click="uploadFiles()" :disabled="files.length === 0 || isUploading"
                            class="px-5 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 disabled:opacity-50 transition-all duration-300 shadow-md hover:shadow-xl">
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
        // no inline Alpine logic here—your mediaLibrary.js handles it!
    </script>
@endpush
