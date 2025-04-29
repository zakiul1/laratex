@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Product Categories</h1>
            <a href="{{ route('product-taxonomies.create') }}"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Category
                </span>
            </a>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="p-4 bg-green-50 text-green-700 border-l-4 border-green-500 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table Section -->
        <div class="overflow-hidden bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-gray-100 text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 font-semibold">#</th>
                            <th class="px-6 py-4 font-semibold">Name</th>
                            <th class="px-6 py-4 font-semibold">Slug</th>
                            <th class="px-6 py-4 font-semibold">Parent</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Images</th>
                            <th class="px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxonomies as $i => $tx)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                <td class="px-6 py-4 font-medium text-gray-600">{{ $taxonomies->firstItem() + $i }}</td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ data_get($tx, 'term.name', 'Unnamed') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ data_get($tx, 'term.slug', '-') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ data_get($tx, 'parentTaxonomy.term.name', '—') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $tx->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $tx->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if ($tx->images->isNotEmpty())
                                        <button x-data
                                            x-on:click="$dispatch('open-image-modal', { images: @js($tx->images->pluck('path')), name: '{{ data_get($tx, 'term.name', 'Unnamed') }}' })"
                                            class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold hover:bg-indigo-200 transition duration-300 ease-in-out">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4-4m0 0l4 4m-4-4v9m8-13h-4m4 0h-4m4 0v9"></path>
                                            </svg>
                                            {{ $tx->images->count() }}
                                        </button>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4-4m0 0l4 4m-4-4v9m8-13h-4m4 0h-4m4 0v9"></path>
                                            </svg>
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="{{ route('product-taxonomies.edit', $tx->term_taxonomy_id) }}"
                                        class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition duration-300 ease-in-out">
                                        Edit
                                    </a>
                                    <form action="{{ route('product-taxonomies.destroy', $tx->term_taxonomy_id) }}"
                                        method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition duration-300 ease-in-out">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7h18M3 11h18M3 15h18M3 19h18"></path>
                                        </svg>
                                        <span>No categories found.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $taxonomies->links('vendor.pagination.tailwind') }}
        </div>

        <!-- Image Preview Modal -->
        <div x-data="{ showModal: false, images: [], currentImage: 0, name: '' }"
            x-on:open-image-modal.window="{ showModal: true, images: $event.detail.images, currentImage: 0, name: $event.detail.name }"
            x-on:close-modal.window="showModal = false" x-show="showModal"
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full mx-4 p-6 relative transform transition-transform duration-300"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="scale-95"
                x-transition:enter-end="scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="scale-100" x-transition:leave-end="scale-95">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800" x-text="name + ' Images'"></h2>
                    <button x-on:click="showModal = false"
                        class="text-gray-500 hover:text-gray-700 transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Image Display -->
                <div class="relative">
                    <img :src="images[currentImage]" alt="Category Image"
                        class="w-full h-96 object-contain rounded-lg shadow-md">
                    <!-- Navigation Arrows -->
                    <button x-show="images.length > 1"
                        x-on:click="currentImage = (currentImage - 1 + images.length) % images.length"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-full transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <button x-show="images.length > 1" x-on:click="currentImage = (currentImage + 1) % images.length"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-full transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Thumbnail Gallery -->
                <div class="mt-4 flex space-x-2 overflow-x-auto pb-2">
                    <template x-for="(image, index) in images" :key="index">
                        <img :src="image" alt="Thumbnail"
                            class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2"
                            :class="{
                                'border-indigo-500': index === currentImage,
                                'border-transparent': index !==
                                    currentImage
                            }"
                            x-on:click="currentImage = index">
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
