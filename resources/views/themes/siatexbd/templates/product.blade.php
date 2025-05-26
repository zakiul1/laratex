@extends('themes.siatexbd.layout')

@section('content')
    @php
        $media = $product->featuredMedia->first();
        $mediaUrl = $media ? $media->getUrl() : '';
        $detailUrl = route('products.show', $product->slug);
    @endphp

    <div class="bg-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li>/</li>
                    @if ($category)
                        <li>
                            <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                {{ $category->name }}
                            </a>
                        </li>
                        <li>/</li>
                    @endif
                    <li class="font-semibold">{{ $product->name }}</li>
                </ol>
            </nav>

            {{-- Product Detail Card --}}
            <div class="bg-white rounded-lg shadow p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

                    {{-- Left Column --}}
                    <div class="space-y-4">
                        <div class="w-16 h-1 bg-red-600"></div>
                        @if ($category)
                            <p class="text-sm uppercase text-gray-500">{{ $category->name }}</p>
                        @endif
                        <h1 class="text-3xl md:text-[32px] font-bold text-blue-800">
                            {{ $product->name }}
                        </h1>
                        <p class="text-gray-700 leading-relaxed">
                            @if (!empty($product->excerpt))
                                {!! nl2br(e($product->excerpt)) !!}
                            @elseif (!empty($product->description))
                                {!! nl2br(e(Str::limit(strip_tags($product->description), 3000, '…'))) !!}
                            @else
                                No description available.
                            @endif
                        </p>
                        {{-- Get Price --}}
                        <button type="button"
                            class="get-price-btn inline-block bg-blue-800 text-white px-6 py-3 hover:bg-blue-900 transition"
                            data-id="{{ $product->id }}" data-title="{{ e($product->name) }}"
                            data-image="{{ $mediaUrl }}" data-url="{{ $detailUrl }}">
                            Get Price
                        </button>
                    </div>

                    {{-- Right Column --}}
                    <div>
                        @if ($media)
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                <x-responsive-image :media="$media" class="w-full h-full object-cover"
                                    alt="{{ $product->name }}" />
                            </div>
                        @else
                            <div class="w-full h-80 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                —
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Page Content (if any) --}}
            <div class="prose max-w-none">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>

            {{-- include the global cart UI --}}
            @include('partials.dynamic-cart')

        </div>
    </div>
@endsection
