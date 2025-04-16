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

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        @if(count($themes) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($themes as $theme)
                <div x-data="{ previewOpen: false }" class="border rounded p-4 shadow-sm bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="capitalize font-medium text-lg">{{ $theme['name'] }}</span>
                            @if($activeTheme === $theme['folder'])
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Active</span>
                            @endif
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-2">
                            {{ $theme['description'] ?? 'No description available.' }}
                        </p>
            
                        @if(file_exists(public_path($theme['screenshot'])))
                            <img src="{{ asset($theme['screenshot']) }}" alt="{{ $theme['name'] }}"
                                class="rounded max-w-full mb-3 cursor-pointer hover:opacity-80"
                                @click="previewOpen = true">
                        @endif
            
                        <!-- Preview Modal -->
                        <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
                            <div class="bg-white rounded-lg p-6 max-w-2xl w-full relative">
                                <button @click="previewOpen = false" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
                                <h2 class="text-xl font-semibold mb-4">{{ $theme['name'] }} Preview</h2>
                                @if(file_exists(public_path($theme['screenshot'])))
                                    <img src="{{ asset($theme['screenshot']) }}" alt="{{ $theme['name'] }}" class="w-full rounded shadow">
                                @else
                                    <p class="text-gray-500 text-sm">No preview available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
            
                    <div class="flex justify-between items-center mt-4 gap-2">
                        @if($activeTheme !== $theme['folder'])
                            <form action="{{ route('themes.activate', $theme['folder']) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                    Activate
                                </button>
                            </form>
            
                            <form action="{{ route('themes.destroy', $theme['folder']) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        @else
                            <span class="text-sm text-gray-500 italic">In use</span>
                            <button type="button" disabled class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded cursor-not-allowed">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
            
            </div>
        @else
            <div class="text-center text-gray-500 mt-12">
                <p>No themes found in <code>resources/views/themes/</code>.</p>
                <p class="mt-2">You can upload a theme using the button above.</p>
            </div>
        @endif
    </div>
@endsection
