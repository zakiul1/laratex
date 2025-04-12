@extends('layouts.dashboard')

@section('content')
    <div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">All Sliders</h2>
            <a href="{{ route('sliders.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                + Add New Slider
            </a>
        </div>

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
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $slider->title }}</td>
                            <td class="px-4 py-2 capitalize">{{ $slider->layout }}</td>
                            <td class="px-4 py-2">
                                @if ($slider->images->first())
                                    <img src="{{ asset('storage/' . $slider->images->first()->image) }}" alt="Preview"
                                        class="w-20 h-14 object-cover rounded border" />
                                @else
                                    <span class="text-gray-400">No image</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex space-x-2">
                                <a href="{{ route('sliders.edit', $slider->id) }}"
                                    class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">
                                    Edit
                                </a>
                                <form action="{{ route('sliders.destroy', $slider->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                        Delete
                                    </button>
                                </form>
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
    </div>
@endsection