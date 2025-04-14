@extends('layouts.dashboard')

@section('content')
    <div x-data="{ modalImage: '', showModal: false }">
        <div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">All Sliders</h2>
                <a href="{{ route('sliders.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                    + Add New Slider
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" class="mb-4">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full md:w-1/3 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Search by title or layout..." />
            </form>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full border divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-700">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Layout</th>
                            <th class="px-4 py-2">Image Preview</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($sliders as $slider)
                            <tr>
                                <td class="px-4 py-2">
                                    {{ $loop->iteration + ($sliders->currentPage() - 1) * $sliders->perPage() }}
                                </td>
                                <td class="px-4 py-2">{{ $slider->title }}</td>
                                <td class="px-4 py-2 capitalize">{{ $slider->layout }}</td>
                                <td class="px-4 py-2">
                                    @if ($slider->images->first())
                                        <img src="{{ asset('storage/' . $slider->images->first()->image) }}"
                                            @click="modalImage = '{{ asset('storage/' . $slider->images->first()->image) }}'; showModal = true"
                                            class="w-20 h-14 object-cover rounded border cursor-pointer hover:opacity-80 transition" />
                                    @else
                                        <span class="text-gray-400">No image</span>
                                    @endif
                                </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('sliders.edit', $slider->id) }}"
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">
                                        Edit
                                    </a>

                                    <button onclick="deleteSlider({{ $slider->id }}, this)"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                        Delete
                                    </button>
                                </div>
                            </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No sliders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $sliders->appends(request()->query())->links() }}
            </div>
        </div>

        <!-- Image Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50"
            @click.away="showModal = false">
            <div class="bg-white p-4 rounded shadow-lg max-w-3xl w-full">
                <img :src="modalImage" class="w-full max-h-[80vh] object-contain rounded" />
            </div>
        </div>
    </div>

    <script>
        function deleteSlider(id, el) {
           //console.log(id);
           
           // console.log(id);
            fetch(`/admin/sliders/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                  //  console.log(response);
                    if (response.ok) {
                        el.closest('tr').remove(); // Remove row from table
                        alert('Slider deleted successfully!');
                    } else {
                        alert('Failed to delete slider.');
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Error deleting slider.');
                });
        }
    </script>

@endsection