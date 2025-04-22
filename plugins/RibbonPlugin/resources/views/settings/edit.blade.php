@extends('layouts.dashboard')

@section('content')
    <div class=" mx-auto p-6 bg-white rounded-lg shadow space-y-6">
        <h2 class="text-xl font-semibold">Ribbon Settings</h2>

        @if (session('success'))
            <div class="px-4 py-2 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('ribbon-plugin.settings.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium">Left Text</label>
                <input type="text" name="left_text" value="{{ old('left_text', $ribbon->left_text ?? '') }}"
                    class="w-full border rounded p-2 @error('left_text') border-red-500 @enderror">
                @error('left_text')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-medium">Center Text</label>
                <input type="text" name="center_text" value="{{ old('center_text', $ribbon->center_text ?? '') }}"
                    class="w-full border rounded p-2 @error('center_text') border-red-500 @enderror">
                @error('center_text')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $ribbon->phone ?? '') }}"
                        class="w-full border rounded p-2 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium">Email</label>
                    <input type="email" name="email" value="{{ old('email', $ribbon->email ?? '') }}"
                        class="w-full border rounded p-2 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium">Background Color</label>
                    <input type="color" name="bg_color" value="{{ old('bg_color', $ribbon->bg_color ?? '#0A4979') }}"
                        class="w-full h-10 p-0 border rounded @error('bg_color') border-red-500 @enderror">
                    @error('bg_color')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium">Text Color</label>
                    <input type="color" name="text_color"
                        value="{{ old('text_color', $ribbon->text_color ?? '#ffffff') }}"
                        class="w-full h-10 p-0 border rounded @error('text_color') border-red-500 @enderror">
                    @error('text_color')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium">Height (px)</label>
                    <input type="number" name="height" min="1" value="{{ old('height', $ribbon->height ?? 32) }}"
                        class="w-full border rounded p-2 @error('height') border-red-500 @enderror">
                    @error('height')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $ribbon->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4">
                <label class="font-medium">Activate Ribbon</label>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save Settings
            </button>
        </form>
    </div>
@endsection
