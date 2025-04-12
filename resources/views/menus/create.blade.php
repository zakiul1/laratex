@extends('layouts.dashboard')

@section('content')
    <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Add New Menu</h2>

        <form method="POST" action="{{ route('menus.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 focus:outline-none focus:ring focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}"
                    class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Parent Menu</label>
                <select name="parent_id"
                    class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700">
                    <option value="">None</option>
                    @foreach($menus as $menu)
                        <option value="{{ $menu->id }}">{{ $menu->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Icon (optional)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. heroicon name"
                    class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700">
            </div>

            <div class="mb-6">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Position</label>
                <select name="position"
                    class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700">
                    <option value="left" {{ old('position') == 'left' ? 'selected' : '' }}>Left</option>
                    <option value="center" {{ old('position') == 'center' ? 'selected' : '' }}>Center</option>
                    <option value="right" {{ old('position') == 'right' ? 'selected' : '' }}>Right</option>
                </select>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('menus.index') }}"
                    class="mr-4 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border rounded hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</a>
                <button type="submit"
                    class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded shadow">Save</button>
            </div>
        </form>
    </div>
@endsection