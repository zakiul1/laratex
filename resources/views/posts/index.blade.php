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

        {{-- Search Only --}}
        <div class="bg-white shadow rounded p-4">
            <input x-model="search" type="text" placeholder="Search title…"
                class="w-full border rounded px-3 py-2 focus:ring focus:border-blue-500" />
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
                        <th class="p-3 text-left">Categories</th>
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
                            {{-- Image or “—” --}}
                            <td class="p-2">
                                <template x-if="post.featured_image">
                                    <img :src="`/storage/${post.featured_image}`" class="h-10 w-10 object-cover rounded" />
                                </template>
                                <template x-if="!post.featured_image">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>

                            {{-- Title --}}
                            <td class="p-2" x-text="post.title"></td>

                            {{-- Categories --}}
                            <td class="p-2">
                                <template x-if="post.taxonomies.length">
                                    <span x-text="post.taxonomies.map(c => c.term.name).join(', ')"></span>
                                </template>
                                <template x-if="!post.taxonomies.length">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>

                            {{-- Status --}}
                            <td class="p-2 capitalize" x-text="post.status"></td>

                            {{-- Created --}}
                            <td class="p-2"
                                x-text="(new Date(post.created_at)).toLocaleDateString('en-US',{
                                    month:'short',day:'numeric',year:'numeric'
                                })">
                            </td>

                            {{-- Actions --}}
                            <td class="p-2 text-right space-x-2">
                                <a :href="viewUrl(post)" target="_blank" class="text-green-600 hover:underline">View</a>
                                <a :href="`/admin/posts/${post.id}/edit`" class="text-indigo-600 hover:underline">Edit</a>
                                <button @click="confirmDelete(post.id)" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredData().length === 0">
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                No posts found.
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
            const baseUrl = "{{ url('admin/posts') }}";

            return {
                // only load real “post” items here:
                posts: @json($postsAll->where('type', 'post')->values()),

                search: '',
                sortKey: 'created_at',
                sortDir: 'desc',
                currentPage: 1,
                perPage: 10,

                viewUrl(post) {
                    return `/posts/${post.slug}`;
                },

                filteredData() {
                    // always reset to page 1 on new filtering
                    this.currentPage = 1;
                    return this.posts.filter(p =>
                        !this.search ||
                        p.title.toLowerCase().includes(this.search.toLowerCase())
                    );
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
                    return Math.max(1, Math.ceil(this.filteredData().length / this.perPage));
                },

                sortBy(field) {
                    if (this.sortKey === field) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortKey = field;
                        this.sortDir = 'asc';
                    }
                },

                confirmDelete(id) {
                    // you can replace this with a custom modal if you like,
                    // but we’ll stick with the built-in confirm for now
                    if (!confirm('Are you sure you want to delete this post?')) return;
                    this.deletePost(id);
                },

                async deletePost(id) {
                    const res = await fetch(`${baseUrl}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    if (res.ok) {
                        // remove from front-end list immediately
                        this.posts = this.posts.filter(p => p.id !== id);
                        if (this.currentPage > this.totalPages()) {
                            this.currentPage = this.totalPages();
                        }
                        ntfy('Post deleted successfully');
                    } else {
                        ntfy('Failed to delete post', 'error');
                    }
                },
            }
        }
    </script>
@endpush
