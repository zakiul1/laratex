{{-- resources/views/themes/customize/partials/typography.blade.php --}}
<div class="border rounded shadow-sm overflow-hidden">
    <!-- Section Header -->
    <button type="button" @click="toggleSection('typography')"
        class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 hover:bg-gray-200">
        <span class="font-medium">Typography &amp; Colors</span>
        <svg :class="{ 'rotate-180': open==='typography' }" class="h-4 w-4 transform transition-transform text-gray-600"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Section Content -->
    <div x-show="open==='typography'" x-collapse class="bg-white">
        {{-- Level 1 list --}}
        <template x-if="openSub===null">
            <ul class="divide-y">
                <template x-for="section in typographySections" :key="section.key">
                    <li @click="openSub = section.key"
                        class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-gray-50 text-sm">
                        <span x-text="section.label"></span>
                        <svg class="h-4 w-4 text-gray-400 transform transition-transform" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                </template>
            </ul>
        </template>

        {{-- Level 2 detail --}}
        <template x-if="openSub">
            <div class="px-4 py-4 space-y-4">
                <button type="button" class="text-sm text-gray-600" @click="openSub = null">&larr; Back</button>
                <h3 class="text-lg font-semibold" x-text="currentSectionLabel"></h3>

                {{-- Headings (H1–H6) --}}
                <div x-show="openSub==='headings'" class="space-y-4">
                    @foreach (range(1, 6) as $i)
                        <div class="flex items-center">
                            <label class="w-20 text-sm font-medium text-gray-700">H{{ $i }} Size</label>
                            <div class="flex-grow flex">
                                <input type="number" min="0"
                                    x-model.number="typography.headings.h{{ $i }}"
                                    class="w-full border p-2 text-sm rounded-l focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                <span
                                    class="inline-flex items-center bg-gray-100 border border-l-0 border-gray-300 px-3 rounded-r text-sm text-gray-600">
                                    px
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr class="border-t border-gray-200 my-2" />

                {{-- Strong Weight --}}
                <div x-show="openSub==='strong'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Strong Weight</label>
                    <input type="number" x-model.number="typography.strong.weight"
                        class="w-full border p-2 text-sm rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>

                {{-- Paragraph Line-Height --}}
                <div x-show="openSub==='paragraph'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Paragraph Line-Height</label>
                    <input type="text" x-model="typography.paragraph.line_height"
                        class="w-full border p-2 text-sm rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>

                {{-- List Marker --}}
                <div x-show="openSub==='list'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">List Marker</label>
                    <select x-model="typography.list.marker"
                        class="w-full border p-2 text-sm rounded focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="disc">Disc</option>
                        <option value="circle">Circle</option>
                        <option value="square">Square</option>
                    </select>
                </div>

                {{-- Anchor Color --}}
                <div x-show="openSub==='anchor'" class="space-y-3 flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Link Color</label>
                    <input type="color" x-model="typography.anchor.color" class="w-8 h-8 p-0 border-0 rounded" />
                </div>

                <hr class="border-t border-gray-200 my-2" />

                {{-- Font Family --}}
                <div x-show="openSub==='fontFamily'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Font Family</label>
                    <select name="font_family" x-model="fontFamily"
                        class="w-full border p-2 text-sm rounded focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @php
                            $fonts = [
                                'sans-serif' => 'Sans Serif',
                                'serif' => 'Serif',
                                'monospace' => 'Monospace',
                                "'Poppins', sans-serif" => 'Poppins',
                                "'Open Sans', sans-serif" => 'Open Sans',
                                "'Oswald', sans-serif" => 'Oswald',
                                "'Roboto', sans-serif" => 'Roboto',
                            ];
                        @endphp
                        @foreach ($fonts as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <hr class="border-t border-gray-200 my-2" />

                {{-- Import/Export --}}
                <div x-show="openSub==='importExport'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Export JSON</label>
                    <a href="{{ route('themes.customize.export') }}"
                        class="inline-block text-sm text-blue-600 underline">Download</a>
                </div>
            </div>
        </template>
    </div>
</div>
