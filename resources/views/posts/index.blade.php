@extends('layouts.dashboard')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6" x-data="postsTable()" x-init="currentPage = 1">
        {{-- Title & New Post --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">All Posts</h1>
            <a href="{{ route('posts.create') }}"
                class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Post
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white shadow rounded p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <input x-model="search" type="text" placeholder="Search title…"
                        class="w-full border rounded px-3 py-2 focus:ring focus:border-blue-500" />
                </div>
                <div>
                    <select x-model="filterType" class="w-full border rounded px-3 py-2">
                        <option value="">All Types</option>
                        <template x-for="[key,label] in Object.entries(types)" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <select x-model="filterStatus" class="w-full border rounded px-3 py-2">
                        <option value="">All Statuses</option>
                        <template x-for="[key,label] in Object.entries(statuses)" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <select x-model.number="perPage" class="w-full border rounded px-3 py-2">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button @click="resetFilters()" type="button" class="border px-4 py-2 rounded hover:bg-gray-100">
                        Reset
                    </button>
                </div>
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
                        <th class="p-3 text-left cursor-pointer" @click="sortBy('type')">
                            Type
                            <template x-if="sortKey==='type'">
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
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="post in paginatedData()" :key="post.id">
                        <tr class="border-b even:bg-gray-50 hover:bg-gray-100">
                            <td class="p-2">
                                <template x-if="post.featured_image">
                                    <img :src="`/storage/${post.featured_image}`" class="h-10 w-10 object-cover rounded" />
                                </template>
                                <template x-if="!post.featured_image">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>
                            <td class="p-2" x-text="post.title"></td>
                            <td class="p-2 capitalize" x-text="post.type"></td>
                            <td class="p-2 capitalize" x-text="post.status"></td>
                            <td class="p-2"
                                x-text="(new Date(post.created_at)).toLocaleDateString('en-US', {
                                    month: 'short',
                                    day:   'numeric',
                                    year:  'numeric'
                                })">
                            </td>
                            <td class="p-2 text-right space-x-2">
                                <a :href="viewUrl(post)" target="_blank" class="text-green-600 hover:underline">View</a>
                                <a :href="`/admin/posts/${post.id}/edit`" class="text-indigo-600 hover:underline">Edit</a>
                                <button @click="deletePost(post.id)" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredData().length === 0">
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No posts found.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-end space-x-2">
            <button :disabled="currentPage === 1" @click="currentPage--"
                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">
                &laquo;
            </button>

            <template x-for="page in totalPages()" :key="page">
                <button @click="currentPage = page" x-text="page"
                    :class="{
                        'bg-blue-600 text-white': currentPage === page,
                        'px-3 py-1 border rounded hover:bg-gray-100': true
                    }"></button>
            </template>

            <button :disabled="currentPage === totalPages()" @click="currentPage++"
                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">
                &raquo;
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function postsTable() {
            // Point directly at /admin/posts as the base, then append /{id}
            const baseUrl = "{{ url('admin/posts') }}";

            return {
                posts: @json($postsAll),
                search: '',
                filterType: '',
                filterStatus: '',
                perPage: 10,
                sortKey: 'created_at',
                sortDir: 'desc',
                currentPage: 1,
                types: @json($types),
                statuses: @json($statuses),

                viewUrl(item) {
                    switch (item.type) {
                        case 'page':
                            return `/pages/${item.slug}`;
                        case 'post':
                            return `/posts/${item.slug}`;
                        default:
                            return `/${item.type}s/${item.slug}`;
                    }
                },

                resetFilters() {
                    this.search = '';
                    this.filterType = '';
                    this.filterStatus = '';
                    this.perPage = 10;
                    this.currentPage = 1;
                    this.sortKey = 'created_at';
                    this.sortDir = 'desc';
                },

                filteredData() {
                    return this.posts.filter(post => {
                        const matchesSearch = !this.search ||
                            post.title.toLowerCase().includes(this.search.toLowerCase());
                        const matchesType = !this.filterType || post.type === this.filterType;
                        const matchesStatus = !this.filterStatus || post.status === this.filterStatus;
                        return matchesSearch && matchesType && matchesStatus;
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
                        return (va < vb ? -1 : va > vb ? 1 : 0) *
                            (this.sortDir === 'asc' ? 1 : -1);
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

                async deletePost(id) {
                    if (!confirm('Delete this post?')) return;

                    const url = `${baseUrl}/${id}`;

                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    if (!res.ok) {
                        console.error('Delete failed', res.status, res.statusText);
                        return;
                    }

                    // remove from front-end state
                    this.posts = this.posts.filter(p => p.id !== id);
                    if (this.currentPage > this.totalPages()) {
                        this.currentPage = this.totalPages() || 1;
                    }
                },
            }
        }
    </script>
@endpush
