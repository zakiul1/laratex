{{-- resources/views/pages/form.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use App\Models\Media;

    // Are we editing an existing page?
    $isEdit = isset($page) && $page->exists;

    // Load initial featured image IDs from the JSON column (or old input)
    $initialMediaIds = old('featured_images', (array) ($page->featured_images ?? []));

    // Map each ID to its URL via Storage::url()
    $initialMediaUrls = collect($initialMediaIds)
        ->map(fn($id) => ($m = Media::find($id)) ? Storage::url($m->path) : null)
        ->filter()
        ->values()
        ->toArray();

    // Templates for the dropdown
    $templates = $templates ?? getThemeTemplates();
@endphp

<div class="mx-auto">
    <link rel="stylesheet" href="{{ asset('blockeditor/styles.css') }}">

    <form method="POST" action="{{ $isEdit ? route('pages.update', $page) : route('pages.store') }}"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- LEFT PANEL: Title, Content, SEO --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Title --}}
            <div>
                <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" placeholder="Add title"
                    required
                    class="w-full text-3xl font-semibold border border-gray-300
                           focus:border-primary focus:ring-0 placeholder:text-gray-400" />
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Block Editor --}}
            <div>
                <div id="customAreaBuilder" class="w-full bg-white"></div>
                <textarea id="layoutData" name="content" class="hidden">{{ old('content', $page->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- SEO Fields --}}
            @include('components.seo-fields', ['model' => $page])
        </div>

        {{-- RIGHT PANEL: Settings, Featured Images, Submit --}}
        <div class="space-y-6">
            {{-- Status --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Status</h3>
                <select name="status" class="w-full border rounded p-2">
                    <option value="published"
                        {{ old('status', $page->status ?? 'published') === 'published' ? 'selected' : '' }}>
                        Published
                    </option>
                    <option value="draft" {{ old('status', $page->status ?? '') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Template --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Template</h3>
                <select name="template" class="w-full border rounded p-2">
                    <option value="">Default</option>
                    @foreach ($templates as $key => $label)
                        <option value="{{ $key }}"
                            {{ old('template', $page->template ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('template')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Permalink --}}
            <div class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}"
                    class="w-full border rounded p-2" placeholder="Optional. Auto-generated if blank." />
                @error('slug')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Hidden type field --}}
            <input type="hidden" name="type" value="page">

            {{-- Featured Images (multiple) --}}
            <div x-data='multiMediaPicker({
                ids: @json($initialMediaIds),
                urls: @json($initialMediaUrls)
              })'
                class="border rounded p-4 bg-white shadow">
                <h3 class="font-semibold text-sm mb-2">Featured Images</h3>

                {{-- Previews --}}
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <template x-for="(url, i) in previewUrls" :key="i">
                        <div class="relative">
                            <img :src="url" class="w-full h-24 object-cover rounded border">
                            <button type="button" @click="remove(i)"
                                class="absolute top-1 right-1 bg-white rounded-full text-red-600 hover:bg-red-100">
                                ×
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Add Images --}}
                <button type="button" @click="openMedia()"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm mb-2">
                    Add Images
                </button>

                {{-- Hidden inputs for IDs --}}
                <template x-for="id in previewIds" :key="id">
                    <input type="hidden" name="featured_images[]" :value="id">
                </template>
                @error('featured_images.*')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded shadow">
                    {{ $isEdit ? 'Update Page' : 'Publish Page' }}
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Media Browser Modal --}}
@include('media.browser-modal')

@push('scripts')
    <script src="{{ asset('blockeditor/bundle.js') }}"></script>
    <script>
        function multiMediaPicker({
            ids,
            urls
        }) {
            return {
                previewIds: [...ids],
                previewUrls: [...urls],

                openMedia() {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: true,
                            onSelect: items => {
                                const arr = Array.isArray(items) ? items : [items];
                                arr.forEach(item => {
                                    if (!this.previewIds.includes(item.id)) {
                                        this.previewIds.push(item.id);
                                        this.previewUrls.push(item.url);
                                    }
                                });
                            }
                        }
                    }));
                },

                remove(i) {
                    this.previewIds.splice(i, 1);
                    this.previewUrls.splice(i, 1);
                }
            };
        }
    </script>
@endpush
