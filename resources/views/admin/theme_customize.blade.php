{{-- resources/views/theme/customize.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">

        <!-- Customization Form -->
        <div class="col-span-1 bg-white p-6 rounded shadow" x-data="themeCustomizer()" @input.debounce.500="previewChanges">

            <h2 class="text-xl font-semibold mb-4">
                Customize Theme: <span class="capitalize">{{ getActiveTheme() }}</span>
            </h2>

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('themes.customize.update') }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf

                <div class="space-y-4">

                    {{-- Site Identity --}}
                    <div class="border rounded shadow-sm">
                        <button type="button" @click="toggleSection('identity')"
                            class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
                            <span class="font-medium">Site Identity</span>
                            <!-- down/up chevron -->
                            <svg :class="{ 'rotate-180': open==='identity' }" class="h-4 w-4 transform transition-transform"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open==='identity'" x-collapse class="px-4 py-4 space-y-4">
                            <label class="block font-medium">Logo</label>
                            <input type="file" name="logo" class="w-full border p-2 rounded">
                            @if ($settings->logo)
                                <img src="{{ asset('storage/' . $settings->logo) }}" class="h-10 mt-2" alt="Logo">
                            @endif
                        </div>
                    </div>

                    {{-- Typography & Colors --}}
                    <div class="border rounded shadow-sm">
                        <button type="button" @click="toggleSection('typography')"
                            class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
                            <span class="font-medium">Typography & Colors</span>
                            <svg :class="{ 'rotate-180': open==='typography' }"
                                class="h-4 w-4 transform transition-transform" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open==='typography'" x-collapse class="px-4 py-4">

                            {{-- Level 1: list of subsections --}}
                            <template x-if="openSub===null">
                                <ul class="divide-y">
                                    <template x-for="section in typographySections" :key="section.key">
                                        <li @click="openSub = section.key"
                                            class="py-2 px-2 flex justify-between items-center cursor-pointer hover:bg-gray-50">
                                            <span x-text="section.label"></span>
                                            <!-- right-pointing chevron -->
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </li>
                                    </template>
                                </ul>
                            </template>

                            {{-- Level 2: subsection detail --}}
                            <template x-if="openSub">
                                <div>
                                    <button type="button" class="text-sm text-gray-600 mb-3" @click="openSub=null">
                                        &larr; Back
                                    </button>
                                    <h3 class="text-lg font-semibold mb-4" x-text="currentSectionLabel"></h3>

                                    {{-- Headings --}}
                                    <div x-show="openSub==='headings'" class="space-y-3">
                                        <label class="block text-sm">H1 Size</label>
                                        <input type="text" name="typography[headings][h1_size]"
                                            class="w-full border p-2 rounded">
                                    </div>

                                    {{-- Strong --}}
                                    <div x-show="openSub==='strong'" class="space-y-3">
                                        <label class="block text-sm">Strong Weight</label>
                                        <input type="number" name="typography[strong][weight]"
                                            class="w-full border p-2 rounded">
                                    </div>

                                    {{-- Paragraph --}}
                                    <div x-show="openSub==='paragraph'" class="space-y-3">
                                        <label class="block text-sm">Paragraph Line-Height</label>
                                        <input type="text" name="typography[paragraph][line_height]"
                                            class="w-full border p-2 rounded">
                                    </div>

                                    {{-- List --}}
                                    <div x-show="openSub==='list'" class="space-y-3">
                                        <label class="block text-sm">List Marker</label>
                                        <select name="typography[list][marker]" class="w-full border p-2 rounded">
                                            <option value="disc">Disc</option>
                                            <option value="circle">Circle</option>
                                            <option value="square">Square</option>
                                        </select>
                                    </div>

                                    {{-- Anchor --}}
                                    <div x-show="openSub==='anchor'" class="space-y-3">
                                        <label class="block text-sm">Link Color</label>
                                        <input type="color" name="typography[anchor][color]"
                                            class="w-12 h-8 p-0 border-0">
                                    </div>

                                    {{-- Import/Export --}}
                                    <div x-show="openSub==='importExport'" class="space-y-3">
                                        <label class="block text-sm">Export JSON</label>
                                        <a href="{{ route('themes.customize.export') }}"
                                            class="inline-block text-blue-600 underline">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>

                    {{-- Footer & Custom CSS --}}
                    <div class="border rounded shadow-sm">
                        <button type="button" @click="toggleSection('footer')"
                            class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
                            <span class="font-medium">Footer & Custom CSS</span>
                            <svg :class="{ 'rotate-180': open==='footer' }" class="h-4 w-4 transform transition-transform"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open==='footer'" x-collapse class="px-4 py-4 space-y-4">
                            <label class="block font-medium">Footer Text</label>
                            <input type="text" name="footer_text" x-model="footerText"
                                class="w-full border p-2 rounded">

                            <label class="block font-medium">Custom CSS</label>
                            <textarea name="custom_css" x-model="customCss" rows="4" class="w-full border p-2 rounded font-mono text-xs"></textarea>
                        </div>
                    </div>

                </div>

                {{-- Save --}}
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded">
                    Save Settings
                </button>
            </form>

            {{-- Reset --}}
            <form method="POST" action="{{ route('themes.customize.reset') }}" class="mt-4"
                onsubmit="return confirm('Reset to default?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded">
                    Reset to Default
                </button>
            </form>
        </div>

        <!-- Live Preview -->
        <div class="col-span-2 bg-white rounded shadow overflow-hidden">
            <iframe x-ref="previewFrame" src="{{ url('/') }}" class="w-full h-[600px] border-0"></iframe>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Alpine collapse plugin --}}
    <script src="//unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => Alpine.plugin(AlpineCollapse))

        function themeCustomizer() {
            return {
                open: 'identity',
                openSub: null,

                typographySections: [{
                        key: 'headings',
                        label: 'Headings'
                    },
                    {
                        key: 'strong',
                        label: 'Strong'
                    },
                    {
                        key: 'paragraph',
                        label: 'Paragraph'
                    },
                    {
                        key: 'list',
                        label: 'List'
                    },
                    {
                        key: 'anchor',
                        label: 'Anchor'
                    },
                    {
                        key: 'importExport',
                        label: 'Import/Export'
                    },
                ],

                primaryColor: '{{ $settings->primary_color ?? '#0d6efd' }}',
                fontFamily: '{{ $settings->font_family ?? 'sans-serif' }}',
                footerText: @json($settings->footer_text ?? ''),
                customCss: @json($settings->custom_css ?? ''),

                toggleSection(section) {
                    this.open = this.open === section ? null : section;
                    this.openSub = null;
                },

                get currentSectionLabel() {
                    const s = this.typographySections.find(x => x.key === this.openSub);
                    return s ? s.label : '';
                },

                previewChanges() {
                    const iframe = this.$refs.previewFrame;
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!doc) return;

                    // remove old preview styles
                    doc.querySelectorAll('[data-live-preview]').forEach(e => e.remove());

                    // inject base vars
                    const base = `
            :root { --primary-color: ${this.primaryColor}; }
            body { font-family: ${this.fontFamily}; }
          `;
                    let style = doc.createElement('style');
                    style.setAttribute('data-live-preview', '');
                    style.textContent = base;
                    doc.head.appendChild(style);

                    // inject custom CSS
                    if (this.customCss) {
                        let css = doc.createElement('style');
                        css.setAttribute('data-live-preview', '');
                        css.textContent = this.customCss;
                        doc.head.appendChild(css);
                    }

                    // update footer text
                    const foot = doc.querySelector('footer');
                    if (foot) foot.innerText = this.footerText;
                }
            }
        }
    </script>
@endpush
