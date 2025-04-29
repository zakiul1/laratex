@extends('layouts.dashboard')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6" x-data="pagesTable('{{ url('admin/pages') }}')" x-init="currentPage = 1">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">All Pages</h1>
            <a href="{{ route('pages.create') }}"
                class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Page
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white shadow rounded p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <input x-model="search" type="text" placeholder="Search title…"
                    class="w-full border rounded px-3 py-2 focus:ring focus:border-blue-500" />

                <select x-model="filterStatus" class="w-full border rounded px-3 py-2">
                    <option value="">All Statuses</option>
                    <template x-for="[key,label] in Object.entries(statuses)" :key="key">
                        <option :value="key" x-text="label"></option>
                    </template>
                </select>

                <select x-model.number="perPage" class="w-full border rounded px-3 py-2">
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                    <option value="100">100 / page</option>
                </select>

                <button @click="resetFilters()" type="button" class="border px-4 py-2 rounded hover:bg-gray-100">
                    Reset
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 font-semibold">
                    <tr>
                        <th class="p-3 text-left">Image</th>
                        <th class="p-3 text-left cursor-pointer" @click="sortBy('title')">
                            Title
                            <template x-if="sortKey==='title'">
                                <span x-text="sortDir==='asc' ? '▲' : '▼'"></span>
                            </template>
                        </th>
                        <th class="p-3 text-left cursor-pointer" @click="sortBy('status')">
                            Status
                            <template x-if="sortKey==='status'">
                                <span x-text="sortDir==='asc' ? '▲' : '▼'"></span>
                            </template>
                        </th>
                        <th class="p-3 text-left cursor-pointer" @click="sortBy('created_at')">
                            Created
                            <template x-if="sortKey==='created_at'">
                                <span x-text="sortDir==='asc' ? '▲' : '▼'"></span>
                            </template>
                        </th>
                        <th class="p-3 text-left">Template</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="page in paginatedData()" :key="page.id">
                        <tr class="border-b even:bg-gray-50 hover:bg-gray-100">
                            <td class="p-2">
                                <template x-if="page.featured_image">
                                    <img :src="`/storage/${page.featured_image}`" class="h-10 w-10 object-cover rounded" />
                                </template>
                                <template x-if="!page.featured_image">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>
                            <td class="p-2" x-text="page.title"></td>
                            <td class="p-2 capitalize" x-text="page.status"></td>
                            <td class="p-2"
                                x-text="new Date(page.created_at)
                                    .toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})">
                            </td>
                            <td class="p-2" x-text="page.template || 'Default'"></td>
                            <td class="p-2 text-right space-x-2">
                                <a :href="`/pages/${page.slug}`" target="_blank" rel="noopener"
                                    class="text-green-600 hover:underline">
                                    View
                                </a>
                                <a :href="`/admin/pages/${page.id}/edit`" class="text-indigo-600 hover:underline">
                                    Edit
                                </a>
                                <button @click="deletePage(page.id)" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredData().length === 0">
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                No pages found.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-end space-x-2">
            <button :disabled="currentPage === 1" @click="currentPage--"
                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">
                «
            </button>
            <template x-for="n in totalPages()" :key="n">
                <button @click="currentPage = n" x-text="n"
                    :class="{
                        'bg-blue-600 text-white': currentPage === n,
                        'px-3 py-1 border rounded hover:bg-gray-100': true
                    }"></button>
            </template>
            <button :disabled="currentPage === totalPages()" @click="currentPage++"
                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">
                »
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function pagesTable(deleteBase) {
            return {
                posts: @json($pagesAll),
                search: '',
                filterStatus: '',
                perPage: 10,
                sortKey: 'created_at',
                sortDir: 'desc',
                currentPage: 1,
                statuses: @json($statuses),

                resetFilters() {
                    this.search = '';
                    this.filterStatus = '';
                    this.perPage = 10;
                    this.currentPage = 1;
                    this.sortKey = 'created_at';
                    this.sortDir = 'desc';
                },

                filteredData() {
                    return this.posts.filter(p => {
                        const matchSearch = !this.search ||
                            p.title.toLowerCase().includes(this.search.toLowerCase());
                        const matchStatus = !this.filterStatus ||
                            p.status === this.filterStatus;
                        return matchSearch && matchStatus;
                    });
                },

                sortedData() {
                    return this.filteredData().sort((a, b) => {
                        let va = a[this.sortKey],
                            vb = b[this.sortKey];
                        if (this.sortKey === 'created_at') {
                            va = new Date(va);
                            vb = new Date(vb);
                        }
                        return ((va < vb ? -1 : va > vb ? 1 : 0) *
                            (this.sortDir === 'asc' ? 1 : -1));
                    });
                },

                paginatedData() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.sortedData().slice(start, start + this.perPage);
                },

                totalPages() {
                    return Math.ceil(this.filteredData().length / this.perPage);
                },

                sortBy(field) {
                    if (this.sortKey === field) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortKey = field;
                        this.sortDir = 'asc';
                    }
                },

                async deletePage(id) {
                    if (!confirm('Delete this page?')) return;

                    try {
                        const response = await fetch(`/admin/pages/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        this.posts = this.posts.filter(p => p.id !== id);

                        if (this.currentPage > this.totalPages()) {
                            this.currentPage = this.totalPages() || 1;
                        }

                        alert('Page deleted successfully!');
                    } catch (error) {
                        console.error('Error deleting page:', error);
                        alert('Failed to delete page. Please try again.');
                    }
                },
            }
        }
    </script>
@endpush
