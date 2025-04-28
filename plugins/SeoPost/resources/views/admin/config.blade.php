{{-- plugins/SeoPost/resources/views/admin/config.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<div class=" mx-auto p-8 bg-gray-50 rounded-2xl shadow-lg mt-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">SeoPost Shortcode Generator</h1>

    @if(session('shortcode'))
        <div class="mb-8 bg-green-100 p-4 rounded-lg shadow-sm">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Generated Shortcode</label>
            <div class="relative">
                <textarea readonly
                          class="w-full border border-gray-200 rounded-lg p-3 bg-white text-gray-600 focus:ring-2 focus:ring-green-500"
                          rows="3">{{ session('shortcode') }}</textarea>
                <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"
                        class="absolute right-2 top-2 px-3 py-1 bg-green-500 text-white rounded-md text-sm hover:bg-green-600 transition">
                    Copy
                </button>
            </div>
        </div>
    @endif

    <form action="{{ route('seopost.generate') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Category ID --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Category ID</label>
            <input type="number" name="cat" value="{{ old('cat', $defaults['cat']) }}"
                   class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            @error('cat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Columns --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Columns (Desktop)</label>
                <input type="number" name="column" required
                       value="{{ old('column', $defaults['column']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('column') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tablet Columns</label>
                <input type="number" name="tcol" required
                       value="{{ old('tcol', $defaults['tcol']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('tcol') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Columns</label>
                <input type="number" name="mcol" required
                       value="{{ old('mcol', $defaults['mcol']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('mcol') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Image, Icon, BG, Get-Price toggles --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach(['img' => 'Image', 'icon' => 'Icon', 'bg' => 'Background Highlight', 'get-price' => 'Get Price'] as $field => $label)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $label }}</label>
                    <select name="{{ $field }}" required
                            class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
                        <option value="yes" {{ old($field, $defaults[$field]) === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ old($field, $defaults[$field]) === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                    @error($field) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>

        {{-- Ordering --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Order By</label>
                <input type="text" name="orderby" required
                       value="{{ old('orderby', $defaults['orderby']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('orderby') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Order Direction</label>
                <select name="order" required
                        class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
                    <option value="ASC" {{ old('order', $defaults['order']) === 'ASC' ? 'selected' : '' }}>Ascending</option>
                    <option value="DESC" {{ old('order', $defaults['order']) === 'DESC' ? 'selected' : '' }}>Descending</option>
                </select>
                @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Style --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Style</label>
            <select name="style" required
                    class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
                @foreach($styles as $style)
                    <option value="{{ $style }}"
                            {{ old('style', $defaults['style']) === $style ? 'selected' : '' }}>
                        {{ ucfirst($style) }}
                    </option>
                @endforeach
            </select>
            @error('style') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Other attributes --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Taxonomy</label>
                <input type="text" name="taxo"
                       value="{{ old('taxo', $defaults['taxo']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('taxo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Custom Class</label>
                <input type="text" name="c-class"
                       value="{{ old('c-class', $defaults['c-class']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('c-class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Optional Post ID & Excerpt --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Post ID</label>
                <input type="number" name="post-id"
                       value="{{ old('post-id', $defaults['post-id']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('post-id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt Length</label>
                <input type="number" name="excerpt-hide"
                       value="{{ old('excerpt-hide', $defaults['excerpt-hide']) }}"
                       class="w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                @error('excerpt-hide') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8">
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">
                Generate Shortcode
            </button>
        </div>
    </form>
</div>
@endsection