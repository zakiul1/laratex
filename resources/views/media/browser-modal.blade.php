{{-- resources/views/media-browser.blade.php --}}
<div x-data="mediaBrowser()" x-init="init()" x-show="isOpen" x-cloak
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

        <!-- Filters: Category & Sort -->
        <div class="px-4 py-2 flex items-center space-x-4 border-b border-gray-200">
            <!-- Category Filter -->
            <select x-model="filterCategory" @change="loadLibrary(1)"
                class="rounded-lg border border-gray-300 px-3 py-2">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>

            <!-- Sort Order -->
            <select x-model="sortOrder" @change="loadLibrary(1)" class="rounded-lg border border-gray-300 px-3 py-2">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name_asc">Name A–Z</option>
                <option value="name_desc">Name Z–A</option>
            </select>
        </div>

        <!-- Tabs -->
        <div class="px-4 py-2 border-b border-gray-200 flex space-x-4">
            <button @click="tab = 'library'"
                :class="tab === 'library' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600 hover:text-blue-600'"
                class="px-4 py-2 font-medium transition-colors">
                Library
            </button>
            {{--     <button @click="tab = 'upload'"
                :class="tab === 'upload' ?
                    'border-b-2 border-blue-600 text-blue-600' :
                    'text-gray-600 hover:text-blue-600'"
                class="px-4 py-2 font-medium transition-colors">
                Upload
            </button> --}}
        </div>

        <!-- Body -->
        <div class="p-6 flex-1 overflow-y-auto">

            <!-- LIBRARY TAB -->
            <div x-show="tab==='library'" class="space-y-6">

                <!-- Selected & Insert -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2 overflow-x-auto py-2">
                        <template x-for="img in images.filter(i => selectedIds.includes(i.id))" :key="img.id">
                            <div class="w-16 h-16 relative flex-shrink-0 bg-white border rounded-lg shadow-sm">
                                <img :src="img.thumbnail" class="w-full h-full object-contain p-1" />
                                <button @click="toggleSelect(img)"
                                    class="absolute -top-2 -right-2 bg-white rounded-full text-red-600 text-xs p-1 shadow hover:bg-red-100">
                                    ×
                                </button>
                            </div>
                        </template>
                        <template x-if="selectedIds.length===0">
                            <span class="text-gray-400 italic">No images selected</span>
                        </template>
                    </div>
                    <button @click="insertSelected()" :disabled="selectedIds.length === 0"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50">
                        Insert Selected (<span x-text="selectedIds.length"></span>)
                    </button>
                </div>

                <!-- Loading & Error -->
                <div x-show="loadError" class="bg-red-100 text-red-700 p-4 rounded-lg" x-text="loadError"></div>
                <div x-show="isLoading" class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <!-- Image Grid -->
                <div x-show="!isLoading" class="grid gap-2 justify-center"
                    style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                    <template x-for="img in images" :key="img.id">
                        <div @click="toggleSelect(img)"
                            :class="selectedIds.includes(img.id) ?
                                'border-blue-500 scale-105' :
                                'border-gray-200 hover:border-blue-300'"
                            class="w-[150px] h-[150px] overflow-hidden rounded-lg bg-white border-2 cursor-pointer transition-transform">
                            <img :src="img.thumbnail" class="w-full h-full object-cover" loading="lazy" />
                        </div>
                    </template>

                    <template x-if="!isLoading && images.length===0">
                        <p class="col-span-full text-center text-gray-500 py-8">No media found.</p>
                    </template>
                </div>

                <!-- Pagination -->
                <div x-show="!isLoading && images.length>0" class="flex justify-center space-x-2 mt-4">
                    <button @click="loadLibrary(currentPage-1)" :disabled="currentPage === 1"
                        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50">
                        Previous
                    </button>
                    <span class="px-4 py-2 bg-gray-100 rounded-lg">
                        Page <span x-text="currentPage"></span> of <span x-text="lastPage||1"></span>
                    </span>
                    <button @click="loadLibrary(currentPage+1)" :disabled="currentPage === lastPage"
                        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>



        </div>
    </div>
</div>
