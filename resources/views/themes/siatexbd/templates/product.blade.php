@extends('themes.siatexbd.layout')

@section('content')
    @php
        $media = $product->featuredMedia->first();
        $mediaUrl = $media ? $media->getUrl('large') : '';
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
                    <li class="font-semibold" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            {{-- Product Detail Card --}}
            <div class="bg-[#f6f6f6] p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

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
                        <button type="button" aria-label="Get price for {{ $product->name }}"
                            class="get-price-btn inline-block bg-blue-800 text-white px-6 py-3 hover:bg-blue-900 transition"
                            data-id="{{ $product->id }}" data-title="{{ e($product->name) }}"
                            data-image="{{ $mediaUrl }}" data-url="{{ $detailUrl }}">
                            Get Price
                        </button>
                    </div>

                    {{-- Right Column --}}
                    <div>
                        @if ($media)
                            <div class="overflow-hidden " style="aspect-ratio:1/1;">
                                <a href="{{ $detailUrl }}" class="block w-full h-full">
                                    <picture>
                                        {{-- AVIF if supported & generated --}}
                                        @if (function_exists('imageavif') && $media->hasGeneratedConversion('large-avif'))
                                            <source type="image/avif" srcset="{{ $media->getUrl('large-avif') }}"
                                                sizes="(max-width:768px)100vw,50vw">
                                        @endif

                                        {{-- WebP if generated --}}
                                        @if ($media->hasGeneratedConversion('large-webp'))
                                            <source type="image/webp" srcset="{{ $media->getUrl('large-webp') }}"
                                                sizes="(max-width:768px)100vw,50vw">
                                        @endif

                                        {{-- JPEG/PNG fallback --}}
                                        <img src="{{ $media->getUrl('large') }}"
                                            srcset="
                                            {{ $media->getUrl('medium') }} 400w,
                                            {{ $media->getUrl('large') }}  800w
                                          "
                                            sizes="(max-width:768px)100vw,50vw" width="1024" height="576" loading="lazy"
                                            class="w-full h-full object-contain" alt="{{ $product->name }}">
                                    </picture>
                                </a>
                            </div>
                        @else
                            <div class="w-full h-80 flex items-center justify-center text-gray-400">
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

        </div>
    </div>
@endsection
