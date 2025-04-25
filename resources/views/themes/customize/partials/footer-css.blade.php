{{-- resources/views/themes/customize/partials/footer.blade.php --}}
<div class="border rounded shadow-sm overflow-hidden">
    <!-- Section Header -->
    <button type="button" @click="toggleSection('footer')"
        class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 hover:bg-gray-200">
        <span class="font-medium">Footer &amp; Custom CSS</span>
        <svg :class="{ 'rotate-180': open==='footer' }" class="h-4 w-4 transform transition-transform text-gray-600"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Section Content -->
    <div x-show="open==='footer'" x-collapse class="bg-white px-4 py-4 space-y-4">
        {{-- Footer Text --}}
        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Footer Text</label>
            <input type="text" name="footer_text" x-model="footerText"
                value="{{ old('footer_text', data_get($settings->options, 'footer_text', '')) }}"
                class="block w-full text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <hr class="border-t border-gray-200 my-2" />

        {{-- Custom CSS --}}
        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Custom CSS</label>
            <textarea name="custom_css" x-model="customCss" rows="4"
                class="block w-full text-xs font-mono border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">{{ old('custom_css', data_get($settings->options, 'custom_css', '')) }}</textarea>
        </div>
    </div>
</div>
