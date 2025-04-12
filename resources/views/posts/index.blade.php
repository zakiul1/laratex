@extends('layouts.dashboard')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">All Posts</h1>
            <a href="{{ route('posts.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">+ New Post</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-2 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto bg-white rounded shadow">
        <table class="w-full min-w-max table-auto border border-gray-200">
            <thead class="bg-gray-100 text-left text-sm font-semibold">
                <tr>
                    <th class="p-4 border-b">Image</th> {{-- NEW --}}
                    <th class="p-4 border-b">Title</th>
                    <th class="p-4 border-b">Type</th>
                    <th class="p-4 border-b">Status</th>
                    <th class="p-4 border-b text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">
                            @if ($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="Preview"
                                    class="h-12 w-12 object-cover rounded border" />
                            @else
                                <span class="text-gray-400 text-sm">No Image</span>
                            @endif
                        </td>
                        <td class="p-4">{{ $post->title }}</td>
                        <td class="p-4 capitalize">{{ $post->type }}</td>
                        <td class="p-4 capitalize">{{ $post->status }}</td>
                        <td class="p-4 text-right space-x-2">
                            <a href="{{ route('posts.edit', $post->id) }}"
                                class="inline-block text-sm text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-sm text-gray-500">No posts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>
@endsection