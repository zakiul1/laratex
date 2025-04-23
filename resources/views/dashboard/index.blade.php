@extends('layouts.dashboard')

@section('content')
    <div class="px-6 py-8">

        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Welcome back, {{ Auth::user()->name }}!
            </h1>
            <p class="text-gray-600">Here's a quick overview of your site.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <x-lucide-file-text class="w-8 h-8 text-indigo-500" />
                <div class="ml-4">
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalPosts }}</p>
                    <p class="text-gray-500">Total Posts</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <x-lucide-book-open class="w-8 h-8 text-green-500" />
                <div class="ml-4">
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalPages }}</p>
                    <p class="text-gray-500">Total Pages</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <x-lucide-tag class="w-8 h-8 text-yellow-500" />
                <div class="ml-4">
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalCategories }}</p>
                    <p class="text-gray-500">Categories</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <x-lucide-shopping-cart class="w-8 h-8 text-red-500" />
                <div class="ml-4">
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalProducts }}</p>
                    <p class="text-gray-500">Products</p>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <a href="{{ route('posts.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-4 flex flex-col items-center">
                    <x-lucide-plus-circle class="w-6 h-6 mb-2" />
                    <span>Create Post</span>
                </a>
                <a href="{{ route('pages.create') }}"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-lg p-4 flex flex-col items-center">
                    <x-lucide-plus-circle class="w-6 h-6 mb-2" />
                    <span>Create Page</span>
                </a>
                <a href="{{ route('media.index') }}"
                    class="bg-yellow-600 hover:bg-[#b45309]
          text-white rounded-lg p-4 flex flex-col items-center">
                    <x-lucide-image class="w-6 h-6 mb-2" />
                    <span>Upload Media</span>
                </a>

                <a href="{{ route('menus.index') }}"
                    class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-4 flex flex-col items-center">
                    <x-lucide-menu class="w-6 h-6 mb-2" />
                    <span>Manage Menus</span>
                </a>
            </div>
        </div>

        <!-- Recent Posts Table -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Posts</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Created</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPosts as $post)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ Str::limit($post->title, 40) }}</td>
                                <td class="px-4 py-3 capitalize">{{ $post->status }}</td>
                                <td class="px-4 py-3">{{ $post->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('posts.edit', $post) }}"
                                        class="text-indigo-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
