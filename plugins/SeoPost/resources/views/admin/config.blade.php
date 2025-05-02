{{-- plugins/SeoPost/resources/views/admin/config.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="mx-auto max-w-4xl p-6 bg-white rounded-xl shadow-lg mt-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">SeoPost Shortcode Generator</h1>

        @if (session('shortcode'))
            <div class="mb-6 bg-green-50 p-4 rounded-lg shadow-sm">
                <label class="block text-sm font-medium text-gray-700 mb-2">Generated Shortcode</label>
                <div class="relative">
                    <textarea readonly
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm bg-gray-50 text-gray-600 focus:ring-2 focus:ring-green-400 focus:border-transparent resize-none"
                        rows="2">{{ session('shortcode') }}</textarea>
                    <button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"
                        class="absolute right-2 top-2 px-2 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600 transition">
                        Copy
                    </button>
                </div>
            </div>
        @endif

        <form action="{{ route('seopost.generate') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Category ID --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                <input type="number" name="cat" value="{{ old('cat', $settings['cat']) }}"
                    class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                @error('cat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Columns --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desktop Columns</label>
                    <input type="number" name="column" value="{{ old('column', $settings['column']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('column')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tablet Columns</label>
                    <input type="number" name="tcol" value="{{ old('tcol', $settings['tcol']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('tcol')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Columns</label>
                    <input type="number" name="mcol" value="{{ old('mcol', $settings['mcol']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('mcol')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Toggles: img, icon, bg, get-price --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach (['img' => 'Image', 'icon' => 'Icon', 'bg' => 'Background Highlight', 'get-price' => 'Get Price'] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                        <select name="{{ $field }}"
                            class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50">
                            <option value="yes" {{ old($field, $settings[$field]) === 'yes' ? 'selected' : '' }}>Yes
                            </option>
                            <option value="no" {{ old($field, $settings[$field]) === 'no' ? 'selected' : '' }}>No
                            </option>
                        </select>
                        @error($field)
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            {{-- Ordering --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order By</label>
                    <input type="text" name="orderby" value="{{ old('orderby', $settings['orderby']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('orderby')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Direction</label>
                    <select name="order"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50">
                        <option value="ASC" {{ old('order', $settings['order']) === 'ASC' ? 'selected' : '' }}>Ascending
                        </option>
                        <option value="DESC" {{ old('order', $settings['order']) === 'DESC' ? 'selected' : '' }}>
                            Descending</option>
                    </select>
                    @error('order')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Style --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Style</label>
                <select name="style"
                    class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50">
                    @foreach ($styles as $style)
                        <option value="{{ $style }}"
                            {{ old('style', $settings['style']) === $style ? 'selected' : '' }}>
                            {{ ucfirst($style) }}
                        </option>
                    @endforeach
                </select>
                @error('style')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Other attributes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taxonomy</label>
                    <input type="text" name="taxo" value="{{ old('taxo', $settings['taxo']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('taxo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Custom Class</label>
                    <input type="text" name="c-class" value="{{ old('c-class', $settings['c-class']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('c-class')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Optional Post ID & Excerpt --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Post ID</label>
                    <input type="number" name="post-id" value="{{ old('post-id', $settings['post-id']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('post-id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt Length</label>
                    <input type="number" name="excerpt-hide" value="{{ old('excerpt-hide', $settings['excerpt-hide']) }}"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50" />
                    @error('excerpt-hide')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition shadow-md text-sm font-medium">
                    Generate Shortcode
                </button>
            </div>
        </form>
    </div>
@endsection
