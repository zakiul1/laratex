{{-- plugins/SliderPlugin/resources/views/admin/sliders/form.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    @php
        // Prepare initial items for Alpine
        $initialItems = $items ?: [
            [
                'id' => null,
                'existing_image_path' => null,
                'media_id' => null,
                'media_preview_url' => null,
                'content' => ['title' => '', 'subtitle' => '', 'buttons' => []],
            ],
        ];
    @endphp

    <script>
        window.initialSliderItems = @json($initialItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    </script>

    <div class="mx-auto space-y-6">
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
            x-data="sliderForm(window.initialSliderItems)" class="space-y-8 bg-white p-6 rounded-xl shadow">
            @csrf
            @if ($slider->exists)
                @method('PUT')
            @endif

            {{-- BASIC SETTINGS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <label class="block mb-1">Name</label>
                    <input name="name" value="{{ old('name', $slider->name) }}"
                        class="w-full border rounded p-2 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block mb-1">Slug</label>
                    <input name="slug" value="{{ old('slug', $slider->slug) }}"
                        class="w-full border rounded p-2 @error('slug') border-red-500 @enderror">
                    @error('slug')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Layout --}}
                <div class="md:col-span-3">
                    <label class="block mb-1">Layout</label>
                    <select name="layout" x-model="sliderLayout"
                        class="w-full border rounded p-2 @error('layout') border-red-500 @enderror">
                        <option value="pure">Pure</option>
                        <option value="with-content">With Content</option>
                    </select>
                    @error('layout')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location --}}
                <div class="md:col-span-3">
                    <label class="block mb-1">Location</label>
                    <select name="location" class="w-full border rounded p-2 @error('location') border-red-500 @enderror">
                        <option value="header">Header</option>
                        <option value="footer">Footer</option>
                        <option value="sidebar">Sidebar</option>
                    </select>
                    @error('location')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Heading & Slogan (only if with-content) --}}
                <template x-if="sliderLayout==='with-content'">
                    <div class="md:col-span-3 space-y-4">
                        <div>
                            <label class="block mb-1">Slider Heading</label>
                            <textarea name="heading" x-model="sliderHeading" class="w-full border rounded p-2" rows="2">{{ old('heading', $slider->heading) }}</textarea>
                            @error('heading')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Slider Slogan</label>
                            <textarea name="slogan" x-model="sliderSlogan" class="w-full border rounded p-2" rows="2">{{ old('slogan', $slider->slogan) }}</textarea>
                            @error('slogan')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </template>

                {{-- Toggles --}}
                <div class="md:col-span-3 flex space-x-6">
                    @foreach ([
            'show_indicators' => 'Indicators',
            'show_arrows' => 'Arrows',
            'is_active' => 'Active',
        ] as $field => $label)
                        <label class="flex items-center space-x-2">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                {{ old($field, $slider->$field) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- SLIDES REPEATER --}}
            <div class="space-y-6">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 p-4 flex justify-between">
                            <strong>Slide <span x-text="idx+1"></span></strong>
                            <button @click.prevent="remove(idx)" class="text-red-600">Remove Slide</button>
                        </div>

                        {{-- MEDIA PREVIEW --}}
                        <div class="relative bg-gray-100 p-4">
                            <template x-if="item.media_preview_url">
                                <img :src="item.media_preview_url" class="max-w-full h-auto object-contain mx-auto"
                                    alt="Preview" />
                            </template>
                            <template x-if="!item.media_preview_url && item.existing_image_path">
                                <img :src="'/storage/' + item.existing_image_path"
                                    class="max-w-full h-auto object-contain mx-auto" alt="Existing" />
                            </template>

                            <button type="button" @click="openMediaLibrary(idx)"
                                class="absolute inset-0 flex items-center justify-center bg-black/30 text-white font-semibold">
                                Select Media
                            </button>

                            {{-- store chosen media id --}}
                            <input type="hidden" x-bind:name="'items[' + idx + '][media_id]'" x-model="item.media_id">
                        </div>

                        {{-- CONTENT FIELDS --}}
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <div class="md:col-span-2 space-y-2">
                                <template x-for="(btn,bidx) in item.content.buttons" :key="bidx">
                                    <div class="flex items-center space-x-2">
                                        <input type="text"
                                            x-bind:name="'items[' + idx + '][content][buttons][' + bidx + '][text]'"
                                            x-model="btn.text" placeholder="Button Text"
                                            class="flex-1 border rounded p-2" />
                                        <input type="url"
                                            x-bind:name="'items[' + idx + '][content][buttons][' + bidx + '][url]'"
                                            x-model="btn.url" placeholder="URL" class="flex-1 border rounded p-2" />
                                        <button @click.prevent="removeButton(idx,bidx)" class="text-red-600">×</button>
                                    </div>
                                </template>
                                <button @click.prevent="addButton(idx)" class="text-blue-600 hover:underline text-sm">+
                                    Add Button</button>
                            </div>
                        </div>
                    </div>
                </template>

                <button @click.prevent="addSlide()" class="px-4 py-2 bg-green-600 text-white rounded">+ Add Slide</button>
            </div>

            {{-- SUBMIT --}}
            <button type="submit" class="mt-4 w-full bg-blue-600 text-white py-3 rounded">
                {{ $slider->exists ? 'Update Slider Plugin' : 'Create Slider Plugin' }}
            </button>
        </form>

        {{-- media browser modal --}}
        @include('media.browser-modal')
    </div>
@endsection

@push('scripts')
    <script>
        function sliderForm(initial) {
            return {
                items: initial.map(i => ({
                    ...i,
                    media_id: i.media_id || null,
                    media_preview_url: i.media_preview_url || null,
                })),
                sliderLayout: '{{ old('layout', $slider->layout) }}',
                sliderHeading: {!! json_encode(old('heading', $slider->heading)) !!},
                sliderSlogan: {!! json_encode(old('slogan', $slider->slogan)) !!},

                openMediaLibrary(idx) {
                    document.dispatchEvent(new CustomEvent('media-open', {
                        detail: {
                            multiple: false,
                            onSelect: media => {
                                this.items[idx].media_id = media.id;
                                this.items[idx].media_preview_url = media.thumbnail;
                            }
                        }
                    }));
                },

                addSlide() {
                    this.items.push({
                        id: null,
                        existing_image_path: null,
                        media_id: null,
                        media_preview_url: null,
                        content: {
                            title: '',
                            subtitle: '',
                            buttons: []
                        }
                    });
                },
                remove(i) {
                    this.items.splice(i, 1)
                },
                addButton(i) {
                    this.items[i].content.buttons.push({
                        text: '',
                        url: ''
                    })
                },
                removeButton(i, b) {
                    this.items[i].content.buttons.splice(b, 1)
                },
            }
        }
    </script>
@endpush
