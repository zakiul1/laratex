@extends('layouts.dashboard')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow" x-data="{
        type: '{{ old('widget_type', 'text') }}',
        contentValue: '{{ old('content', '') }}'
    }">
        <h2 class="text-xl font-bold mb-4">Add Widget</h2>

        <form method="POST" action="{{ route('widgets.store') }}">
            @csrf

            <!-- Title -->
            <div class="mb-4">
                <label for="title" class="block font-medium mb-1">Title</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}"
                    class="w-full border p-2 rounded @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Widget Type -->
            <div class="mb-4">
                <label for="widget_type" class="block font-medium mb-1">Widget Type</label>
                <select id="widget_type" name="widget_type" x-model="type"
                    class="w-full border p-2 rounded @error('widget_type') border-red-500 @enderror">
                    <option value="text">Text</option>
                    <option value="view">Blade View</option>
                    <option value="menu">Menu</option>
                    <option value="category">Category</option>
                </select>
                @error('widget_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- TEXT / VIEW -->
            <template x-if="type==='text' || type==='view'">
                <div class="mb-4">
                    <label class="block font-medium mb-1" x-text="type==='view' ? 'View Name' : 'Content'"></label>
                    <textarea x-model="contentValue" name="dummy" rows="4"
                        class="w-full border p-2 rounded @error('content') border-red-500 @enderror"></textarea>
                    @error('content')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </template>

            <!-- MENU -->
            <template x-if="type==='menu'">
                <div class="mb-4">
                    <label class="block font-medium mb-1">Select Menu</label>
                    <select x-model="contentValue"
                        class="w-full border p-2 rounded @error('content') border-red-500 @enderror">
                        <option value="">-- choose --</option>
                        @foreach (\App\Models\Menu::all() as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                        @endforeach
                    </select>
                    @error('content')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </template>

            <!-- CATEGORY -->
            <template x-if="type==='category'">
                <div class="mb-4">
                    <label class="block font-medium mb-1">Select Parent Category</label>
                    <select x-model="contentValue"
                        class="w-full border p-2 rounded @error('content') border-red-500 @enderror">
                        <option value="">-- choose --</option>
                        @foreach (\App\Models\Category::whereNull('parent_id')->get() as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('content')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </template>

            <!-- Hidden content field -->
            <input type="hidden" name="content" :value="contentValue">

            <!-- Widget Area -->
            <div class="mb-4">
                <label for="widget_area" class="block font-medium mb-1">Widget Area</label>
                <select id="widget_area" name="widget_area"
                    class="w-full border p-2 rounded @error('widget_area') border-red-500 @enderror">
                    @foreach (theme_widget_areas() as $key => $label)
                        <option value="{{ $key }}" {{ old('widget_area') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('widget_area')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order -->
            <div class="mb-4">
                <label for="order" class="block font-medium mb-1">Order</label>
                <input id="order" name="order" type="number" value="{{ old('order', 0) }}"
                    class="w-full border p-2 rounded @error('order') border-red-500 @enderror">
                @error('order')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6 flex items-center gap-2">
                <input id="status" name="status" type="checkbox" value="1"
                    {{ old('status', 1) ? 'checked' : '' }}>
                <label for="status" class="text-sm">Active</label>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                Save Widget
            </button>
        </form>
    </div>
@endsection
