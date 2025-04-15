@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar: Category List -->
        <aside class="md:col-span-1">
            <h2 class="text-lg font-semibold mb-4">Categories</h2>
            <ul class="space-y-2">
                @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('category.show', $cat->slug) }}" class="block text-sm hover:text-blue-600">
                            {{ $cat->name }}
                        </a>
                        @if($cat->children && $cat->children->count())
                            <ul class="ml-4 mt-1 space-y-1">
                                @foreach($cat->children as $sub)
                                    <li>
                                        <a href="{{ route('category.show', $sub->slug) }}"
                                            class="text-xs text-gray-500 hover:text-blue-500">
                                            → {{ $sub->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>

        <!-- Main Product View -->
        <main class="md:col-span-3">
            <!-- Breadcrumb -->
        {{--     <nav class="text-sm text-gray-600 mb-6">
                <a href="/" class="hover:underline">Home</a> /
                <a href="{{ route('category.show', $product->category->slug ?? '') }}"
                    class="hover:underline">{{ $product->category->name }}</a> /
                <span class="text-gray-800">{{ $product->name }}</span>
            </nav> --}}

            <!-- Product Detail -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Featured Image -->
                <div>
                    @if($product->featured_image)
                        <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}"
                            class="w-full h-96 object-cover rounded shadow" />
                    @endif

                    @if($product->images && $product->images->count())
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach($product->images as $img)
                                <img src="{{ asset('storage/' . $img->image) }}" class="w-20 h-20 object-cover rounded border" />
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
                    @if($product->price)
                        <p class="text-xl text-blue-600 font-semibold mb-4">$ {{ number_format($product->price, 2) }}</p>
                    @endif
                    <p class="mb-4 text-gray-700">{{ $product->description }}</p>

                    <div class="text-sm text-gray-500">
                        @if($product->stock !== null)
                            <p>Stock: <span class="font-medium text-gray-800">{{ $product->stock }}</span></p>
                        @endif
                        <p>Status:
                            <span class="font-medium {{ $product->status ? 'text-green-600' : 'text-red-500' }}">
                                {{ $product->status ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection