{{-- resources/views/theme/customize/partials/identity.blade.php --}}

<div class="border rounded shadow-sm overflow-hidden"
    x-data='{
       // which section is open
       open: null,

       // existing logo URL or null
       preview: @json($settings->logo ? asset("storage/{$settings->logo}") : null),

       // container width setting (number or null)
       containerWidth: @json(data_get($settings->options, 'container_width', null)),

       // site identity settings
       primaryColor: @json($settings->primary_color),
       siteTitle: @json(data_get($settings->options, 'site_title', config('app.name'))),
       siteTitleColor: @json(data_get($settings->options, 'site_title_color', '#000000')),
       tagline: @json(data_get($settings->options, 'tagline', '')),
       taglineColor: @json(data_get($settings->options, 'tagline_color', '#666666')),
       showTagline: @json(data_get($settings->options, 'show_tagline', false)),

       // toggle open/closed
       toggle() {
         this.open = this.open === "identity" ? null : "identity"
       }
     }'>
    {{-- Section Header --}}
    <button type="button" @click="toggle()"
        class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 hover:bg-gray-200">
        <span class="font-medium">Site Identity</span>
        <svg :class="{ 'rotate-180': open==='identity' }" class="h-4 w-4 transform transition-transform text-gray-600"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Section Content --}}
    <div x-show="open==='identity'" x-collapse class="bg-white">

        {{-- Container Width --}}
        <div class="px-4 py-4 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Container Width (px)</label>
            <input type="number" name="container_width" x-model.number="containerWidth" placeholder="e.g. 1200"
                min="0" class="w-full border p-2 rounded focus:outline-none focus:ring-indigo-500" />
            <p class="text-xs text-gray-500">Leave blank to use default.</p>
        </div>

        {{-- Logo Upload --}}
        <div class="px-4 py-4 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Logo</label>
            <div class="relative group border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-indigo-500"
                @dragover.prevent
                @drop.prevent="
             const file = $event.dataTransfer.files[0];
             $refs.upload.files = $event.dataTransfer.files;
             preview = URL.createObjectURL(file);
           ">
                <input x-ref="upload" type="file" name="logo"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    @change="
                 const file = $event.target.files[0];
                 if (file) preview = URL.createObjectURL(file);
               " />

                <template x-if="preview">
                    <img :src="preview" alt="Logo preview" class="mx-auto h-24 object-contain rounded" />
                </template>

                <template x-if="!preview">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 12v-4m0 0l4 4m-4-4l-4 4M3 12h18" />
                        </svg>
                        <span class="text-sm">Click or drag & drop to upload</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Divider --}}
        <hr class="border-t border-gray-200 my-2 mx-4" />

        {{-- Other Site Identity Fields --}}
        <div class="px-4 py-4 space-y-4">

            <!-- Primary Color -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Primary Color</label>
                <input type="color" name="primary_color" x-model="primaryColor"
                    class="w-8 h-8 p-0 border-0 rounded" />
            </div>

            <!-- Site Title -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Site Title</label>
                <input type="text" name="site_title" x-model="siteTitle" class="w-full border p-2 rounded text-sm" />
            </div>

            <!-- Site Title Color -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Title Color</label>
                <input type="color" name="site_title_color" x-model="siteTitleColor"
                    class="w-8 h-8 p-0 border-0 rounded" />
            </div>

            <!-- Tagline -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Tagline</label>
                <input type="text" name="tagline" x-model="tagline" class="w-full border p-2 rounded text-sm" />
            </div>

            <!-- Tagline Color -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Tagline Color</label>
                <input type="color" name="tagline_color" x-model="taglineColor"
                    class="w-8 h-8 p-0 border-0 rounded" />
            </div>

            <!-- Show Tagline -->
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="show_tagline" x-model="showTagline"
                    class="h-4 w-4 text-indigo-600 rounded border-gray-300" />
                <label class="text-sm text-gray-700">Show Tagline with Site Title</label>
            </div>
        </div>

    </div>
</div>
