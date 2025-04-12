@extends('layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manage Menus</h1>
        <a href="{{ route('menus.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
            + Add New Menu
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg p-4">
        @if($menus->count())
            <ul id="menu-list" class="space-y-3" data-url="{{ route('menus.updateOrder') }}">
                @foreach($menus as $menu)
                    <li class="p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 flex justify-between items-center cursor-move"
                        data-id="{{ $menu->id }}">
                        <div>
                            <div class="font-semibold">{{ $menu->title }}</div>
                            <div class="text-xs text-gray-500">Slug: {{ $menu->slug }}</div>
                            @if ($menu->children->count())
                                <ul class="ml-4 mt-2 space-y-2">
                                    @foreach ($menu->children as $child)
                                        <li class="pl-2 border-l-2 border-blue-500">
                                            <span class="text-sm">{{ $child->title }}</span>
                                            <small class="text-xs text-gray-400">({{ $child->slug }})</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('menus.edit', $menu) }}"
                                class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 text-xs">Edit</a>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                                onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs">Delete</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600 dark:text-gray-300">No menus created yet.</p>
        @endif
    </div>
@endsection