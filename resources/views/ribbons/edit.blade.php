@extends('layouts.dashboard')

@section('content')
    <div class="max-w-xl mx-auto bg-white dark:bg-gray-900 p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Edit Ribbon</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('ribbons.update', $ribbon) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Left Text</label>
                <input type="text" name="left_text" value="{{ old('left_text', $ribbon->left_text) }}"
                    class="w-full border rounded p-2" />
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $ribbon->phone) }}"
                    class="w-full border rounded p-2" />
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email', $ribbon->email) }}"
                    class="w-full border rounded p-2" />
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Background Color</label>
                <input type="color" name="bg_color" value="{{ old('bg_color', $ribbon->bg_color) }}"
                    class="w-16 h-10 border rounded" />
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Text Color</label>
                <input type="color" name="text_color" value="{{ old('text_color', $ribbon->text_color) }}"
                    class="w-16 h-10 border rounded" />
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
        </form>
    </div>
@endsection