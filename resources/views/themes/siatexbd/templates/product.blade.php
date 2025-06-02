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
            <nav class="text-sm text-gray-700" aria-label="Breadcrumb">
                <ol class="flex flex-wrap space-x-2">
                    <li>
                        <a href="{{ route('home') }}" class="hover:underline text-gray-700">Home</a>
                    </li>
                    <li>/</li>
                    @if ($category)
                        <li>
                            <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline text-gray-700">
                                {{ $category->name }}
                            </a>
                        </li>
                        <li>/</li>
                    @endif
                    <li class="font-semibold text-gray-900" aria-current="page">
                        {{ $product->name }}
                    </li>
                </ol>
            </nav>

            {{-- Product Detail Card --}}
            {{-- Note: fixed the background class from "bg-text-gray-200" to "bg-gray-200" --}}
            <div class="bg-gray-200 p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    {{-- Left Column --}}
                    <div class="space-y-4">
                        <div class="w-16 h-1 bg-red-600"></div>

                        @if ($category)
                            <p class="text-sm uppercase text-gray-700">
                                {{ $category->name }}
                            </p>
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
                            <div class="overflow-hidden" style="aspect-ratio:1/1;">
                                <a href="{{ $detailUrl }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="[
                                        150 => 'thumbnail',
                                        300 => 'medium',
                                        480 => 'mobile',
                                        768 => 'tablet',
                                        1024 => 'large',
                                    ]" {{-- on screens ≤768px, the image is treated as 100vw; otherwise 50vw --}}
                                        sizes="(max-width:768px) 100vw, 50vw" width="1024" height="1024" loading="lazy"
                                        class="w-full h-full object-contain" alt="{{ $product->name }}" />
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
