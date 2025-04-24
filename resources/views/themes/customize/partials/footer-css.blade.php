<div class="border rounded shadow-sm">
    <button type="button" @click="toggleSection('footer')"
        class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
        <span class="font-medium">Footer & Custom CSS</span>
        <svg :class="{ 'rotate-180': open==='footer' }" class="h-4 w-4 transform transition-transform" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open==='footer'" x-collapse class="px-4 py-4 space-y-4">
        <label class="block font-medium">Footer Text</label>
        <input type="text" name="footer_text" x-model="footerText"
            value="{{ old('footer_text', data_get($settings->options, 'footer_text', '')) }}"
            class="w-full border p-2 rounded">

        <label class="block font-medium">Custom CSS</label>
        <textarea name="custom_css" x-model="customCss" rows="4" class="w-full border p-2 rounded font-mono text-xs">{{ old('custom_css', data_get($settings->options, 'custom_css', '')) }}</textarea>
    </div>
</div>
