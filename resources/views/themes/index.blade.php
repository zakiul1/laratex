@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Theme Manager</h2>
            <form action="{{ route('themes.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="cursor-pointer bg-gray-100 px-4 py-2 rounded border border-gray-300 hover:bg-gray-200">
                    Upload Theme (.zip)
                    <input type="file" name="theme" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($themes as $theme)
                <div class="bg-white shadow rounded overflow-hidden">
                    <img src="{{ asset($theme['screenshot']) }}" alt="{{ $theme['name'] }}" class="w-full h-48 object-cover">
                    <div class="p-4 space-y-2">
                        <h3 class="text-lg font-bold">{{ $theme['name'] }}</h3>
                        <p class="text-sm text-gray-600">{{ $theme['description'] }}</p>

                        @if ($activeTheme === $theme['folder'])
                            <span class="inline-block px-3 py-1 bg-green-600 text-white text-xs rounded">Active</span>
                        @else
                            <form action="{{ route('themes.activate', $theme['folder']) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="mt-2 px-4 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                                    Activate
                                </button>
                            </form>
                        @endif

                        <div class="flex gap-2 mt-3">
                            <a href="{{ route('themes.preview', $theme['folder']) }}"
                                class="text-xs text-blue-600 underline">Preview Files</a>
                            <form action="{{ route('themes.duplicate', $theme['folder']) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-yellow-600 underline">Duplicate</button>
                            </form>
                            <a href="{{ route('themes.edit', $theme['folder']) }}"
                                class="text-xs text-gray-700 underline">Edit</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection