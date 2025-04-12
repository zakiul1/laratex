@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf

            <!-- Left: Title and Content -->
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Add title"
                        class="w-full text-3xl font-semibold border-0 focus:ring-0 placeholder:text-gray-400" required />
                </div>

                <div>
                    <textarea name="content" rows="12" placeholder="Type / to choose a block"
                        class="w-full border rounded p-4 text-sm text-gray-800">{{ old('content') }}</textarea>
                </div>
            </div>

            <!-- Right: Options Panel -->
            <div class="space-y-6">

                <!-- Status & Visibility -->
                <div class="border rounded p-4 shadow bg-white">
                    <h3 class="font-semibold text-sm mb-2">Status & Visibility</h3>
                    <select name="status" class="w-full border rounded p-2">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                <!-- Template Selection -->
                <div class="border rounded p-4 shadow bg-white">
                    <h3 class="font-semibold text-sm mb-2">Template</h3>
                    @php
                        $templateFiles = collect(File::files(resource_path('views/templates')))
                            ->map(fn($file) => str_replace('.blade.php', '', $file->getFilename()));
                    @endphp
                    <select name="template" class="w-full border rounded p-2">
                        <option value="">Default</option>
                        @foreach ($templateFiles as $template)
                            <option value="{{ $template }}" {{ old('template') === $template ? 'selected' : '' }}>
                                {{ ucfirst($template) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Slug -->
                <div class="border rounded p-4 shadow bg-white">
                    <h3 class="font-semibold text-sm mb-2">Permalink</h3>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="w-full border rounded p-2"
                        placeholder="Optional. Auto-generated if blank." />
                </div>

                <!-- Post Type -->
                <div class="border rounded p-4 shadow bg-white">
                    <h3 class="font-semibold text-sm mb-2">Post Type</h3>
                    <select name="type" class="w-full border rounded p-2">
                        <option value="post" {{ old('type') === 'post' ? 'selected' : '' }}>Post</option>
                        <option value="page" {{ old('type') === 'page' ? 'selected' : '' }}>Page</option>
                        <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <!-- Featured Image -->
                <div x-data="{ imagePreview: '' }" class="border rounded p-4 shadow bg-white"
                    x-init="imagePreview = '{{ old('featured_image') }}'">
                    <h3 class="font-semibold text-sm mb-2">Featured Image</h3>
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-full h-32 object-cover rounded border mb-2" />
                    </template>
                    <input type="file" name="featured_image" @change="
                        const file = $event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => imagePreview = e.target.result;
                            reader.readAsDataURL(file);
                        }
                    " class="w-full text-sm" />
                </div>

                <!-- Meta Fields -->
                <div x-data="{ metas: [{ key: '', value: '' }] }" class="border rounded p-4 shadow bg-white">
                    <h3 class="font-semibold text-sm mb-3">Meta Fields</h3>
                    <template x-for="(meta, index) in metas" :key="index">
                        <div class="flex gap-2 mb-2">
                            <input type="text" :name="`meta_keys[]`" x-model="meta.key" placeholder="Key"
                                class="w-1/2 border rounded p-2 text-sm" />
                            <input type="text" :name="`meta_values[]`" x-model="meta.value" placeholder="Value"
                                class="w-1/2 border rounded p-2 text-sm" />
                        </div>
                    </template>
                    <button type="button" @click="metas.push({ key: '', value: '' })"
                        class="text-xs text-blue-600 hover:underline">+ Add Meta Field</button>
                </div>

                <!-- Publish -->
                <div>
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-3 rounded shadow">
                        Publish
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection