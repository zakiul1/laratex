{{-- resources/views/categories/show.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Models\Category;
@endphp

@extends('themes.classic.layout')

@section('content')
    @php
        // Sidebar: fetch all top‑level categories with their children
        $allCategories = Category::with('children')->whereNull('parent_id')->get();
    @endphp

    <div class="flex max-w-7xl mx-auto px-4 py-8 space-x-6">
        {{-- SIDEBAR --}}
        <aside class="w-64 hidden lg:block">
            <h2 class="text-xl font-bold mb-4">Categories</h2>
            <ul class="space-y-2">
                @foreach ($allCategories as $root)
                    @php
                        $isActiveRoot = $root->id === $category->id || $category->parent_id === $root->id;
                    @endphp
                    <li>
                        <a href="{{ route('categories.show', $root->slug) }}"
                            class="block {{ $isActiveRoot ? 'font-semibold text-gray-900' : 'text-gray-700 hover:text-gray-900' }}">
                            {{ strtoupper($root->name) }}
                        </a>
                        @if ($isActiveRoot && $root->children->isNotEmpty())
                            <ul class="pl-4 mt-2 space-y-1">
                                @foreach ($root->children as $child)
                                    @php $isActiveChild = $child->id === $category->id; @endphp
                                    <li>
                                        <a href="{{ route('categories.show', $child->slug) }}"
                                            class="block {{ $isActiveChild ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-800' }}">
                                            {{ strtoupper($child->name) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1">
            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    @if ($category->parent)
                        <li>/</li>
                        <li>
                            <a href="{{ route('categories.show', $category->parent->slug) }}"
                                class="hover:underline">{{ $category->parent->name }}</a>
                        </li>
                    @endif
                    <li>/</li>
                    <li class="font-bold">{{ $category->name }}</li>
                </ol>
            </nav>

            <h1 class="text-3xl font-bold mb-6">{{ strtoupper($category->name) }}</h1>

            @if ($category->children->isNotEmpty())
                {{-- SUB‑CATEGORIES --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($category->children as $sub)
                        @php $count = $sub->products()->count(); @endphp
                        <a href="{{ route('categories.show', $sub->slug) }}"
                            class="group block relative overflow-hidden rounded-lg shadow-lg">
                            <img src="{{ Storage::disk('public')->exists($sub->featured_image)
                                ? Storage::url($sub->featured_image)
                                : asset('images/placeholder.png') }}"
                                alt="{{ $sub->name }}"
                                class="w-full  object-cover group-hover:scale-105 transition-transform duration-300" />
                            <div class=" ">
                                <div class="w-full text-center py-3 bg-white bg-opacity-80">
                                    <h3 class="font-semibold">{{ strtoupper($sub->name) }}</h3>
                                    <p class="text-sm text-gray-700">
                                        {{ $count }} {{ Str::plural('Product', $count) }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- PRODUCTS --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($products as $product)
                        <a href="{{ route('products.show', $product->slug) }}"
                            class="group block relative overflow-hidden rounded-lg shadow-lg">
                            @php $path = $product->featured_image; @endphp
                            @if ($path && Storage::disk('public')->exists($path))
                                <img src="{{ Storage::url($path) }}" alt="{{ $product->name }}"
                                    class="w-full  object-cover group-hover:scale-105 transition-transform duration-300" />
                            @else
                                <div class="w-full  bg-gray-200 flex items-center justify-center text-gray-500">
                                    No Image
                                </div>
                            @endif
                            <div class="">
                                <div class="w-full text-center py-3 bg-white bg-opacity-80">
                                    <h3 class="font-semibold">{{ $product->name }}</h3>
                                    <p class="text-sm text-gray-700">
                                        ${{ number_format($product->price, 2) }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
