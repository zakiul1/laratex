@extends('layouts.dashboard')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow" x-data="{ type: '{{ old('widget_type', $widget->widget_type ?? 'text') }}' }">
        <h2 class="text-xl font-bold mb-4">{{ isset($widget) ? 'Edit' : 'Add' }} Widget</h2>

        <form method="POST" action="{{ isset($widget) ? route('widgets.update', $widget) : route('widgets.store') }}">
            @csrf
            @if(isset($widget)) @method('PUT') @endif

            <!-- Title -->
            <div class="mb-4">
                <label class="block font-medium mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $widget->title ?? '') }}" class="w-full border p-2 rounded">
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Widget Type -->
            <div class="mb-4">
                <label class="block font-medium mb-1">Widget Type</label>
                <select name="widget_type" x-model="type" class="w-full border p-2 rounded">
                    <option value="text">Text</option>
                    <option value="view">Blade View</option>
                    <option value="menu">Menu</option>
                    <option value="category">Category</option>
                </select>
                @error('widget_type')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content or View -->
            <div class="mb-4" x-show="type === 'text' || type === 'view'">
                <label class="block font-medium mb-1" x-text="type === 'view' ? 'View Name (e.g. partials.newsletter)' : 'Content'"></label>
                <textarea name="content" rows="5" class="w-full border p-2 rounded">{{ old('content', $widget->content ?? '') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Menu Selection -->
            <div class="mb-4" x-show="type === 'menu'">
                <label class="block font-medium mb-1">Select Menu</label>
                <select name="content" class="w-full border p-2 rounded">
                    @foreach (\App\Models\Menu::all() as $menu)
                        <option value="{{ $menu->id }}" {{ old('content', $widget->content ?? '') == $menu->id ? 'selected' : '' }}>
                            {{ $menu->name }}
                        </option>
                    @endforeach
                </select>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category Selection -->
            <div class="mb-4" x-show="type === 'category'">
                <label class="block font-medium mb-1">Select Parent Category</label>
                <select name="content" class="w-full border p-2 rounded">
                    @foreach (\App\Models\Category::whereNull('parent_id')->get() as $category)
                        <option value="{{ $category->slug }}" {{ old('content', $widget->content ?? '') == $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('content')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Widget Area -->
            <div class="mb-4">
                <label class="block font-medium mb-1">Widget Area</label>
                <select name="widget_area" class="w-full border p-2 rounded">
                    @foreach (theme_widget_areas() as $key => $label)
                        <option value="{{ $key }}" {{ old('widget_area', $widget->widget_area ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('widget_area')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order -->
            <div class="mb-4">
                <label class="block font-medium mb-1">Order</label>
                <input type="number" name="order" value="{{ old('order', $widget->order ?? 0) }}" class="w-full border p-2 rounded">
                @error('order')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="status" value="1" {{ old('status', $widget->status ?? 1) ? 'checked' : '' }}>
                <label class="text-sm">Active</label>
                @error('status')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ isset($widget) ? 'Update Widget' : 'Save Widget' }}
            </button>
        </form>
    </div>
@endsection
