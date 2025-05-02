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

        <!-- Tabs -->
        <div class="px-4 py-2 border-b border-gray-200 flex space-x-4">
            <button @click="tab = 'library'"
                :class="tab === 'library' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-blue-600'"
                class="px-4 py-2 font-medium transition-colors">
                Library
            </button>
            <button @click="tab = 'upload'"
                :class="tab === 'upload' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-blue-600'"
                class="px-4 py-2 font-medium transition-colors">
                Upload
            </button>
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
                            @keyup.enter="loadLibrary(1)"
                            class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="loadError" class="bg-red-100 text-red-700 p-4 rounded-lg" x-text="loadError"></div>

                <!-- Selected & Insert -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2 overflow-x-auto py-2">
                        <template x-for="img in images.filter(i => selectedIds.includes(i.id))" :key="img.id">
                            <div class="w-16 h-16 relative flex-shrink-0 bg-white border rounded-lg shadow-sm">
                                <img :src="img.url" class="w-full h-full object-contain p-1" />
                                <button @click="toggleSelect(img)"
                                    class="absolute -top-2 -right-2 bg-white rounded-full text-red-600 text-xs p-1 shadow hover:bg-red-100 transition-colors">
                                    ×
                                </button>
                            </div>
                        </template>
                        <template x-if="selectedIds.length === 0">
                            <span class="text-gray-400 italic">No images selected</span>
                        </template>
                    </div>
                    <button @click="insertSelected()" :disabled="selectedIds.length === 0"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 transition-colors">
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
                            :class="selectedIds.includes(img.id) ?
                                'border-blue-500 scale-105' :
                                'border-gray-200 hover:border-blue-300'"
                            class="relative cursor-pointer rounded-lg overflow-hidden bg-white border-2 transition-transform duration-200">
                            <img :src="img.url" class="w-full h-32 object-cover" loading="lazy" />
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
                <div @dragover.prevent @drop.prevent="handleDrop($event)" @click="$refs.uploadInput.click()"
                    class="border-2 border-dashed border-gray-300 p-8 text-center rounded-lg cursor-pointer hover:border-blue-400 transition-colors">
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
                            <div class="w-16 h-16 border rounded-lg overflow-hidden flex-shrink-0 bg-white">
                                <img :src="file.previewUrl" class="w-full h-full object-contain p-1" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium truncate" x-text="file.name"></span>
                                    <template x-if="file.status === 'uploading' && file.lengthComputable">
                                        <span class="text-xs text-gray-500" x-text="file.progress + '%'"></span>
                                    </template>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-blue-600 transition-all duration-300"
                                        :style="file.lengthComputable ? `width: ${file.progress}%` : ''"></div>
                                </div>
                            </div>
                            <button @click="removeFile(idx)" class="text-red-600 hover:text-red-700">×</button>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>
</div>
