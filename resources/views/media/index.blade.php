@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto py-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Media Library</h1>
            <a href="{{ route('media.create') }}"
                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                <x-lucide-plus class="w-5 h-5 mr-2" />
                Upload New
            </a>
        </div>

        @if ($media->count())
            {{-- Gallery Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach ($media as $m)
                    <div class="relative bg-white rounded-lg shadow overflow-hidden group">

                        {{-- Preview Image --}}
                        @if ($m->url)
                            <img src="{{ $m->url }}" alt="{{ $m->original_name }}" class="w-full h-40 object-cover" />
                        @else
                            <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                                No preview
                            </div>
                        @endif

                        <div class="p-2">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $m->original_name }}</p>
                            <p class="text-xs text-gray-500">ID: {{ $m->id }}</p>
                        </div>

                        {{-- Hover actions --}}
                        <div
                            class="absolute inset-0 bg-black bg-opacity-50 
                                    flex items-center justify-center space-x-2 
                                    opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('media.edit', $m) }}"
                                class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded text-sm">
                                Edit
                            </a>
                            <form action="{{ route('media.destroy', $m) }}" method="POST"
                                onsubmit="return confirm('Delete this file?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $media->links() }}
            </div>
        @else
            <p class="text-gray-500">
                No media found.
                <a href="{{ route('media.create') }}" class="text-blue-600 hover:underline">
                    Upload some files →
                </a>
            </p>
        @endif
    </div>
@endsection
