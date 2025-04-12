@extends('layouts.dashboard')

@section('content')
    <div class="p-6 bg-white dark:bg-gray-900 rounded shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Manage Ribbons</h2>
            <a href="{{ route('ribbons.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Add New</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Left Text</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Phone</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Email</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Colors</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($ribbons as $ribbon)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-2">{{ $ribbon->left_text }}</td>
                            <td class="px-4 py-2">{{ $ribbon->phone }}</td>
                            <td class="px-4 py-2">{{ $ribbon->email }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block w-5 h-5 rounded" style="background: {{ $ribbon->bg_color }}"></span>
                                <span class="inline-block w-5 h-5 rounded ml-2"
                                    style="background: {{ $ribbon->text_color }}"></span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('ribbons.edit', $ribbon) }}"
                                    class="text-blue-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('ribbons.destroy', $ribbon) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:underline text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection