@extends('layouts.dashboard')

@section('content')
    <div class="p-6" x-data="shortcodeBuilder()">
        <h1 class="text-2xl font-bold mb-4">SEO Post Shortcode Builder</h1>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <template x-for="(val, key) in attrs" :key="key">
                <div class="space-y-1">
                    <label class="block text-sm font-medium" x-text="key"></label>
                    <template x-if="isBoolean(key)">
                        <select x-model="attrs[key]" class="w-full border rounded p-1 text-sm">
                            <option value="yes">yes</option>
                            <option value="no">no</option>
                        </select>
                    </template>
                    <template x-if="!isBoolean(key)">
                        <input type="text" x-model="attrs[key]" class="w-full border rounded p-1 text-sm" />
                    </template>
                </div>
            </template>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Generated Shortcode</label>
            <textarea readonly class="w-full h-24 border rounded p-2 font-mono bg-gray-50 text-sm" x-text="shortcode"></textarea>
        </div>

        <button @click="copy()" class="px-4 py-2 bg-blue-600 text-white rounded">Copy to Clipboard</button>
    </div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function shortcodeBuilder() {
            return {
                attrs: @json($defaults),
                isBoolean(key) {
                    return ['img', 'get-price', 'icon', 'bg'].includes(key);
                },
                get shortcode() {
                    let parts = ['[seopost'];
                    for (let k in this.attrs) {
                        let v = this.attrs[k];
                        if (v === '' || v == null) continue;
                        parts.push(`${k}="${v}"`);
                    }
                    parts.push(']');
                    return parts.join(' ');
                },
                copy() {
                    navigator.clipboard.writeText(this.shortcode)
                        .then(() => alert('Copied!'));
                }
            }
        }
    </script>
@endpush
