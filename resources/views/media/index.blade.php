{{-- resources/views/admin/media/index.blade.php --}}
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

    <div x-data="mediaLibrary()" x-init="init()" class="min-h-screen dark:from-gray-900 dark:to-gray-800">

        <!-- Toolbar -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl mb-8">
            <div class="flex flex-wrap gap-6 items-center">
                <div class="flex-1">
                    <input type="text" x-model.debounce.500ms="search" placeholder="Search media…"
                        @keyup.enter="loadMedia(1)"
                        class="w-full rounded-xl px-5 py-3 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 transition shadow-sm hover:shadow-md" />
                </div>
                <select x-model="category" @change="loadMedia(1)"
                    class="rounded-xl px-5 py-3 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 transition shadow-sm hover:shadow-md">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name || 'Unnamed Category'"></option>
                    </template>
                </select>
                <select x-model="perPage" @change="loadMedia(1)"
                    class="rounded-xl px-5 py-3 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 transition shadow-sm hover:shadow-md">
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
                    :class="view === 'grid'
                        ?
                        'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900'"
                    class="px-5 py-2 rounded-xl font-semibold transition shadow-md hover:shadow-lg">Grid</button>
                <button @click="view = 'list'"
                    :class="view === 'list'
                        ?
                        'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900'"
                    class="px-5 py-2 rounded-xl font-semibold transition shadow-md hover:shadow-lg">List</button>
                <button @click="view = 'thumbnail'"
                    :class="view === 'thumbnail'
                        ?
                        'bg-gradient-to-r from-purple-600 to-purple-700 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900'"
                    class="px-5 py-2 rounded-xl font-semibold transition shadow-md hover:shadow-lg">Thumbnail</button>
            </div>
            <div class="flex gap-3">
                <button @click="openUpload()"
                    class="flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white px-5 py-2 rounded-xl font-semibold transition shadow-md hover:shadow-xl">+
                    Upload</button>
                <button @click="bulkDelete()" :disabled="selected.length === 0"
                    class="flex items-center gap-2 bg-gradient-to-r from-red-600 to-red-700 text-white px-5 py-2 rounded-xl font-semibold disabled:opacity-50 transition shadow-md hover:shadow-xl">Delete
                    (<span x-text="selected.length"></span>)</button>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-purple-500"></div>
            <p class="mt-3 text-gray-600 dark:text-gray-300 font-medium">Loading media…</p>
        </div>

        <!-- Media Views -->
        <div x-show="!isLoading" class="space-y-8">

            <!-- Grid View -->
            <div x-show="view === 'grid'"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative group bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition transform hover:-translate-y-1">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-3 left-3 z-10 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm" />

                        <img :src="item.medium"
                            :srcset="`${item.thumbnail} 150w, ${item.medium} 300w, ${item.large} 1024w, ${item.original} ${item.originalWidth}w`"
                            sizes="(max-width:640px)150px,(max-width:1024px)300px,1024px"
                            class="w-full h-48 object-cover rounded-t-xl transition-transform duration-300 group-hover:scale-105"
                            alt="" loading="lazy" />

                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-gradient-to-t from-black/70 to-transparent transition-opacity duration-300">
                            <button @click="showModal(item)"
                                class="bg-white dark:bg-gray-700 p-3 rounded-full shadow-lg mr-3 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium transition">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-red-600 p-3 rounded-full shadow-lg text-white hover:bg-red-700">Delete</button>
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
                        class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl shadow-md p-5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <div class="flex items-center gap-5">
                            <input type="checkbox" x-model="selected" :value="item.id"
                                class="rounded border-gray-300 dark:border-gray-600 shadow-sm" />
                            <img :src="item.thumbnail"
                                class="w-16 h-16 object-cover rounded-lg transition-transform duration-300 hover:scale-105"
                                loading="lazy" />
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="item.filename"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="getCategoryNames(item)"></div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button @click="showModal(item)"
                                class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-2 rounded-lg shadow-sm hover:shadow-md">View</button>
                            <button @click="deleteMedia(item.id)"
                                class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-lg shadow-sm hover:shadow-md">Delete</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Thumbnail View -->
            <div x-show="view === 'thumbnail'" class="flex flex-wrap gap-3">
                <template x-for="item in media" :key="item.id">
                    <div
                        class="relative w-28 h-28 bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden transition hover:shadow-xl hover:scale-105">
                        <input type="checkbox" x-model="selected" :value="item.id"
                            class="absolute top-2 left-2 z-10 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm" />
                        <img :src="item.thumbnail" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                </template>
            </div>

        </div>

        <!-- No Results -->
        <div x-show="!isLoading && media.length === 0 && !loadError" class="text-center py-12">
            <p class="text-gray-600 dark:text-gray-300 font-medium">
                No media found. Try adjusting your search or upload new media.
            </p>
        </div>

        <!-- Pagination -->
        <div x-show="!isLoading && media.length > 0 && !loadError" class="mt-8 flex justify-center gap-3">
            <button @click="loadMedia(currentPage - 1)" :disabled="currentPage === 1"
                class="px-5 py-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-semibold disabled:opacity-50 shadow-md hover:shadow-lg">Previous</button>
            <span
                class="px-5 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl font-semibold shadow-md">
                Page <span x-text="currentPage"></span> of <span x-text="lastPage || 1"></span>
            </span>
            <button @click="loadMedia(currentPage + 1)" :disabled="currentPage === lastPage"
                class="px-5 py-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-semibold disabled:opacity-50 shadow-md hover:shadow-lg">Next</button>
        </div>

        <!-- Preview Modal -->
        <div x-show="modalOpen" x-transition class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-6"
            @click="modalOpen = false">
            <div @click.stop
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-4xl w-full relative overflow-hidden">
                <button @click="modalOpen = false"
                    class="absolute top-4 right-4 bg-white dark:bg-gray-700 rounded-full p-2 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">✕</button>
                <img :src="modalImage.original" class="w-full max-h-[80vh] object-contain rounded-t-2xl" />
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
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl p-8 flex flex-col space-y-6 max-h-[90vh] overflow-y-auto">
                <!-- ... your upload markup remains unchanged ... -->
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // all Alpine logic lives in your mediaLibrary.js
    </script>
@endpush
