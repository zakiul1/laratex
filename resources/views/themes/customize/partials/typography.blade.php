{{-- resources/views/themes/customize/partials/typography.blade.php --}}
<div class="border rounded shadow-sm">
    <button type="button" @click="toggleSection('typography')"
        class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
        <span class="font-medium">Typography & Colors</span>
        <svg :class="{ 'rotate-180': open==='typography' }" class="h-4 w-4 transform transition-transform" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open==='typography'" x-collapse class="px-4 py-4">

        {{-- Level 1 list --}}
        <template x-if="openSub===null">
            <ul class="divide-y">
                <template x-for="section in typographySections" :key="section.key">
                    <li @click="openSub = section.key"
                        class="py-2 px-2 flex justify-between items-center cursor-pointer hover:bg-gray-50">
                        <span x-text="section.label"></span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                </template>
            </ul>
        </template>

        {{-- Level 2 detail --}}
        <template x-if="openSub">
            <div>
                <button type="button" class="text-sm text-gray-600 mb-3" @click="openSub=null">&larr; Back</button>
                <h3 class="text-lg font-semibold mb-4" x-text="currentSectionLabel"></h3>

                {{-- Headings (H1–H6) --}}
                <div x-show="openSub==='headings'" class="space-y-4">
                    @foreach (range(1, 6) as $i)
                        <div class="flex items-center">
                            <label class="w-20 text-sm font-medium">H{{ $i }} Size</label>
                            <div class="flex-grow flex">
                                <input type="number" min="0"
                                    x-model.number="typography.headings.h{{ $i }}"
                                    class="w-full border-t border-b border-l p-2 rounded-l" />
                                <span
                                    class="inline-flex items-center bg-gray-100 border border-l-0 border-gray-300 px-3 rounded-r text-gray-600">
                                    px
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Strong --}}
                <div x-show="openSub==='strong'" class="space-y-3">
                    <label class="block text-sm">Strong Weight</label>
                    <input type="number" x-model.number="typography.strong.weight" class="w-full border p-2 rounded" />
                </div>

                {{-- Paragraph --}}
                <div x-show="openSub==='paragraph'" class="space-y-3">
                    <label class="block text-sm">Paragraph Line-Height</label>
                    <input type="text" x-model="typography.paragraph.line_height"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- List --}}
                <div x-show="openSub==='list'" class="space-y-3">
                    <label class="block text-sm">List Marker</label>
                    <select x-model="typography.list.marker" class="w-full border p-2 rounded">
                        <option value="disc">Disc</option>
                        <option value="circle">Circle</option>
                        <option value="square">Square</option>
                    </select>
                </div>

                {{-- Anchor --}}
                <div x-show="openSub==='anchor'" class="space-y-3">
                    <label class="block text-sm">Link Color</label>
                    <input type="color" x-model="typography.anchor.color" class="w-12 h-8 p-0 border-0 rounded" />
                </div>

                {{-- Font Family --}}
                <div x-show="openSub==='fontFamily'" class="space-y-3">
                    <label class="block text-sm font-medium">Font Family</label>
                    <select name="font_family" x-model="fontFamily" class="w-full border p-2 rounded">
                        @php
                            $fonts = [
                                'sans-serif' => 'Sans Serif',
                                'serif' => 'Serif',
                                'monospace' => 'Monospace',
                                "'Poppins', sans-serif" => 'Poppins',
                                "'Open Sans', sans-serif" => 'Open Sans',
                            ];
                        @endphp
                        @foreach ($fonts as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Import/Export --}}
                <div x-show="openSub==='importExport'" class="space-y-3">
                    <label class="block text-sm">Export JSON</label>
                    <a href="{{ route('themes.customize.export') }}" class="inline-block text-blue-600 underline">
                        Download
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>
