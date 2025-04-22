{{-- plugins/SliderPlugin/resources/views/admin/sliders/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        $initialItems = $items;
        if (empty($initialItems)) {
            $initialItems = [
                [
                    'id' => null,
                    'existing_image_path' => null,
                    'content' => ['title' => '', 'subtitle' => '', 'buttons' => []],
                ],
            ];
        }
    @endphp

    {{-- Expose to JS --}}
    <script>
        window.initialSliderItems = @json($initialItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    </script>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">
                {{ $slider->exists ? 'Edit Slider Plugin' : 'Create Slider Plugin' }}
            </h1>
            <a href="{{ route('slider-plugin.sliders.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                ← Back to list
            </a>
        </div>

        <form method="POST"
            action="{{ $slider->exists ? route('slider-plugin.sliders.update', $slider->id) : route('slider-plugin.sliders.store') }}"
            enctype="multipart/form-data" x-data="sliderForm(window.initialSliderItems)" class="space-y-8">
            @csrf
            @if ($slider->exists)
                @method('PUT')
            @endif

            {{-- ▼ BASIC SETTINGS (name, slug, layout, location, toggles) ▼ --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-white rounded-2xl shadow">
                {{-- name --}}
                <div class="md:col-span-2">
                    <label class="block mb-1">Name</label>
                    <input name="name" value="{{ old('name', $slider->name) }}"
                        class="w-full border rounded p-2 @error('name') border-red-500 @enderror" />
                    @error('name')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- slug --}}
                <div>
                    <label class="block mb-1">Slug</label>
                    <input name="slug" value="{{ old('slug', $slider->slug) }}"
                        class="w-full border rounded p-2 @error('slug') border-red-500 @enderror" />
                    @error('slug')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- layout --}}
                <div class="md:col-span-3">
                    <label class="block mb-1">Layout</label>
                    <select name="layout" class="w-full border rounded p-2 @error('layout') border-red-500 @enderror">
                        <option value="pure" {{ old('layout', $slider->layout) === 'pure' ? 'selected' : '' }}>Pure
                        </option>
                        <option value="with-content"
                            {{ old('layout', $slider->layout) === 'with-content' ? 'selected' : '' }}>
                            With Content</option>
                    </select>
                    @error('layout')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- location --}}
                <div class="md:col-span-3">
                    <label class="block mb-1">Location</label>
                    <select name="location" class="w-full border rounded p-2 @error('location') border-red-500 @enderror">
                        <option value="header" {{ old('location', $slider->location) === 'header' ? 'selected' : '' }}>
                            Header
                        </option>
                        <option value="footer" {{ old('location', $slider->location) === 'footer' ? 'selected' : '' }}>
                            Footer
                        </option>
                        <option value="sidebar" {{ old('location', $slider->location) === 'sidebar' ? 'selected' : '' }}>
                            Sidebar
                        </option>
                    </select>
                    @error('location')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- toggles --}}
                <div class="md:col-span-3 flex items-center space-x-6">
                    @foreach (['show_indicators' => 'Indicators', 'show_arrows' => 'Arrows', 'autoplay' => 'Autoplay', 'is_active' => 'Active'] as $field => $label)
                        <label class="inline-flex items-center">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                {{ old($field, $slider->$field) ? 'checked' : '' }} />
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ▼ SLIDES REPEATER ▼ --}}
            <div class="space-y-6">
                <template x-for="(item,idx) in items" :key="idx">
                    <div class="p-6 bg-white rounded-2xl shadow space-y-4">
                        <div class="flex justify-between items-center">
                            <h2 class="font-semibold">Slide <span x-text="idx+1" /></h2>
                            <button @click.prevent="remove(idx)" class="text-red-600">Remove Slide</button>
                        </div>

                        {{-- image + remove + hidden fields --}}
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="h-32 w-48 bg-gray-100 rounded overflow-hidden">
                                    <template x-if="item.new_image_preview">
                                        <img :src="item.new_image_preview" class="h-full w-full object-cover" />
                                    </template>
                                    <template x-if="!item.new_image_preview && item.existing_image_path">
                                        <img :src="`/storage/${item.existing_image_path}`"
                                            class="h-full w-full object-cover" />
                                    </template>
                                    <template x-if="!item.new_image_preview && !item.existing_image_path">
                                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                                            No Image
                                        </div>
                                    </template>
                                </div>

                                {{-- carry through existing id & path --}}
                                <input type="hidden" x-bind:name="'items[' + idx + '][id]'" x-bind:value="item.id" />
                                <input type="hidden" x-bind:name="'items[' + idx + '][existing_image_path]'"
                                    x-bind:value="item.existing_image_path" />

                                {{-- remove image --}}
                                <template x-if="!item.new_image_preview && item.existing_image_path">
                                    <form method="POST"
                                        x-bind:action="'{{ url('admin/plugins/slider/' . $slider->id . '/item') }}/' + item.id +
                                            '/image'"
                                        onsubmit="return confirm('Remove this image?');"
                                        class="absolute top-1 right-1 z-20">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bg-white p-1 rounded-full shadow hover:bg-gray-100">
                                            <!-- trash icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862
                                       a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0
                                       a1 1 0 011-1h2a1 1 0 011 1" />
                                            </svg>
                                        </button>
                                    </form>
                                </template>

                                {{-- file input overlay --}}
                                <input type="file" x-bind:name="'items[' + idx + '][new_image]'"
                                    class="absolute inset-0 z-10 opacity-0 cursor-pointer"
                                    @change="previewNewImage($event,idx)" />
                            </div>
                            <span class="text-gray-600 text-sm">Click box to change image</span>
                        </div>

                        {{-- title & subtitle --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1">Title</label>
                                <input type="text" x-bind:name="'items[' + idx + '][content][title]'"
                                    x-model="item.content.title" class="w-full border rounded p-2" />
                            </div>
                            <div>
                                <label class="block mb-1">Subtitle</label>
                                <input type="text" x-bind:name="'items[' + idx + '][content][subtitle]'"
                                    x-model="item.content.subtitle" class="w-full border rounded p-2" />
                            </div>
                        </div>

                        {{-- buttons --}}
                        <div class="space-y-2">
                            <template x-for="(btn,bidx) in item.content.buttons" :key="bidx">
                                <div class="flex items-center space-x-2">
                                    <input type="text"
                                        x-bind:name="'items[' + idx + '][content][buttons][' + bidx + '][text]'"
                                        x-model="btn.text" placeholder="Button Text" class="flex-1 border rounded p-2" />
                                    <input type="url"
                                        x-bind:name="'items[' + idx + '][content][buttons][' + bidx + '][url]'"
                                        x-model="btn.url" placeholder="URL" class="flex-1 border rounded p-2" />
                                    <button @click.prevent="removeButton(idx,bidx)" class="text-red-600">×</button>
                                </div>
                            </template>
                            <button @click.prevent="addButton(idx)" class="text-blue-600 hover:underline text-sm">
                                + Add Button
                            </button>
                        </div>
                    </div>
                </template>

                {{-- add slide --}}
                <button @click.prevent="addSlide()" class="px-4 py-2 bg-green-600 text-white rounded">
                    + Add Slide
                </button>
            </div>

            {{-- submit --}}
            <div>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded">
                    {{ $slider->exists ? 'Update Slider Plugin' : 'Create Slider Plugin' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Alpine helper --}}
    <script>
        function sliderForm(initial) {
            return {
                items: initial,
                addSlide() {
                    this.items.push({
                        id: null,
                        existing_image_path: null,
                        new_image_preview: null,
                        content: {
                            title: '',
                            subtitle: '',
                            buttons: []
                        }
                    });
                },
                remove(i) {
                    this.items.splice(i, 1);
                },
                addButton(i) {
                    this.items[i].content.buttons.push({
                        text: '',
                        url: ''
                    });
                },
                removeButton(i, b) {
                    this.items[i].content.buttons.splice(b, 1);
                },
                previewNewImage(e, i) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.items[i].new_image_preview = URL.createObjectURL(file);
                }
            }
        }
    </script>
@endsection
