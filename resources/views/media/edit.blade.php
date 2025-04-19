@extends('layouts.dashboard')

@section('content')
    <div class="max-w-3xl mx-auto mt-6 bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Edit File Metadata</h1>

        <form action="{{ route('media.update', $media) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="original_name" class="block text-sm font-medium text-gray-700">
                    Filename
                </label>
                <input type="text" name="original_name" id="original_name"
                    value="{{ old('original_name', $media->original_name) }}"
                    class="mt-1 block w-full border-gray-300 rounded p-2">
                @error('original_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Save
            </button>
        </form>
    </div>
@endsection
