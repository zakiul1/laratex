{{-- resources/views/categories/index.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        // Build a simple array of only the fields your JS needs
        $jsCategories = collect($categories)->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->term->name,
                'slug' => $cat->term->slug,
                'featured_image' => $cat->featured_image,
                'status' => $cat->status,
            ];
        });
    @endphp

    {{-- Dump into JS global in one @json call --}}
    <script>
        window.categories = @json($jsCategories);
    </script>

    <div class="w-full mx-auto py-6" x-data="categoryTable(window.categories)">

        {{-- Search & Add --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
            <input x-model="search" type="text" placeholder="Search name or slug…"
                class="w-full sm:w-2/3 border rounded p-2">

            <a href="{{ route('categories.create') }}"
                class="inline-block w-full sm:w-auto text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                + Add Category
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2 cursor-pointer" @click="sort('name')">
                            Name <span x-text="sortKey==='name'? (sortAsc?'↑':'↓'):''"></span>
                        </th>
                        <th class="px-4 py-2 cursor-pointer" @click="sort('slug')">
                            Slug <span x-text="sortKey==='slug'? (sortAsc?'↑':'↓'):''"></span>
                        </th>
                        <th class="px-4 py-2">Image</th>
                        <th class="px-4 py-2 cursor-pointer" @click="sort('status')">
                            Status <span x-text="sortKey==='status'? (sortAsc?'↑':'↓'):''"></span>
                        </th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    <template x-for="(cat, idx) in filtered" :key="cat.id">
                        <tr>
                            <td class="px-4 py-2" x-text="idx + 1"></td>
                            <td class="px-4 py-2" x-text="cat.name"></td>
                            <td class="px-4 py-2" x-text="cat.slug"></td>
                            <td class="px-4 py-2">
                                <template x-if="cat.featured_image">
                                    <img :src="`/storage/${cat.featured_image}`" class="w-12 h-12 object-cover rounded">
                                </template>
                                <template x-if="!cat.featured_image">
                                    <span class="text-gray-400">N/A</span>
                                </template>
                            </td>
                            <td class="px-4 py-2" x-text="cat.status ? 'Active' : 'Inactive'"></td>
                            <td class="px-4 py-2">
                                <div class="flex gap-2">
                                    <a :href="`/admin/categories/${cat.id}/edit`"
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">
                                        Edit
                                    </a>
                                    <button @click="remove(cat.id)"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                No categories found.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $paginated->links() }}
        </div>
    </div>

    {{-- Alpine component unchanged --}}
    <script>
        function categoryTable(data) {
            return {
                search: '',
                sortKey: 'name',
                sortAsc: true,
                data: data,
                get filtered() {
                    let f = this.data.filter(cat =>
                        cat.name.toLowerCase().includes(this.search.toLowerCase()) ||
                        cat.slug.toLowerCase().includes(this.search.toLowerCase())
                    );
                    return f.sort((a, b) => {
                        let fa = a[this.sortKey],
                            fb = b[this.sortKey];
                        if (fa < fb) return this.sortAsc ? -1 : 1;
                        if (fa > fb) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                },
                sort(key) {
                    if (this.sortKey === key) this.sortAsc = !this.sortAsc;
                    else {
                        this.sortKey = key;
                        this.sortAsc = true;
                    }
                },
                remove(id) {
                    if (!confirm('Are you sure?')) return;
                    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/categories/${id}`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${token}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        }
    </script>
@endsection
