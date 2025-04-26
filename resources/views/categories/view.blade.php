@extends('layouts.app')

@section('content')
    <div class="flex gap-8 px-6 py-10">
        {{-- Left Sidebar --}}
        <aside class="w-64 bg-gray-50 p-4 rounded shadow-sm">
            <h2 class="text-lg font-bold mb-4">Categories</h2>
            @foreach ($allCategories as $cat)
                <div class="mb-2">
                    <a href="{{ route('category.show', $cat->term->slug) }}"
                        class="block text-sm font-semibold {{ $cat->id === $category->id ? 'text-blue-600' : 'text-gray-700' }}">
                        {{ strtoupper($cat->term->name) }}
                    </a>
                    @if ($cat->children->count())
                        <ul class="ml-3 mt-1 list-disc text-xs text-gray-500">
                            @foreach ($cat->children as $sub)
                                <li>
                                    <a href="{{ route('category.show', $sub->term->slug) }}" class="hover:text-blue-500">
                                        {{ $sub->term->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </aside>

        {{-- Main Content --}}
        <main class="flex-1">
            {{-- Breadcrumb --}}
            <div class="text-sm text-gray-500 mb-2">
                <a href="{{ route('home') }}">Home</a> / {{ strtoupper($category->term->name) }}
            </div>

            {{-- Category Title --}}
            <h1 class="text-3xl font-bold mb-6">{{ strtoupper($category->term->name) }}</h1>

            {{-- Products Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <a href="{{ route('product.show', $product->slug) }}"
                        class="group relative overflow-hidden rounded shadow">
                        <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->title }}"
                            class="w-full h-80 object-cover" />
                        <div class="absolute bottom-0 w-full bg-black/70 text-white text-center py-2">
                            <span
                                class="block font-bold text-sm tracking-wide uppercase">{{ strtoupper($product->title) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </main>
    </div>
@endsection
