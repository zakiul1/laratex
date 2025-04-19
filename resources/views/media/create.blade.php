@extends('layouts.dashboard')
@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Upload Media</h1>
        <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
            x-data="bulkUploader()" x-init="init()">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Folder</label>
                <select name="folder_id" class="w-1/3 border rounded px-2 py-1">
                    <option value="">— None —</option>
                    @foreach ($folders as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="dropzone"
                class="border-2 border-dashed border-gray-300 rounded p-8 text-center text-gray-500 cursor-pointer"
                @drop.prevent="handleDrop($event)" @dragover.prevent @click="$refs.input.click()">
                <p x-show="!files.length">Drag & drop files here, or click to select</p>
                <template x-for="(f,i) in files" :key="i">
                    <p class="text-sm mt-2" x-text="f.name"></p>
                </template>
                <input type="file" name="files[]" accept="image/*" multiple class="hidden" x-ref="input"
                    @change="handleFiles($event)" />
            </div>

            <button type="submit" :disabled="!files.length"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded disabled:opacity-50">Upload</button>
        </form>
    </div>

    @push('scripts')
        <script>
            function bulkUploader() {
                return {
                    files: [],
                    init() {
                        // nothing
                    },
                    handleFiles(e) {
                        this.files = Array.from(e.target.files);
                    },
                    handleDrop(e) {
                        this.files = Array.from(e.dataTransfer.files);
                    }
                }
            }
        </script>
    @endpush
@endsection
