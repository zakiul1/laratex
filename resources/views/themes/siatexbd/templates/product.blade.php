{{-- resources/views/themes/siatexbd/templates/product.blade.php --}}
@extends('themes.siatexbd.layout')

@section('content')
    @php
        use Illuminate\Support\Str;

        // If your controller passed $category, it should be a TermTaxonomy instance or null.
        // We’ll build media data for the product image as before.
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
                        <a href="{{ route('home') }}" class="hover:underline text-gray-700">
                            Home
                        </a>
                    </li>
                    <li>/</li>

                    {{-- Only show category link if $category and its term exist --}}
                    @if (isset($category) && $category?->term)
                        <li>
                            <a href="{{ route('categories.show', $category->term->slug) }}"
                                class="hover:underline text-gray-700">
                                {{ $category->term->name }}
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
            <div class="bg-gray-100 p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    {{-- Left Column (description + “Get Price” button) --}}
                    <div class="space-y-4">
                        <div class="w-16 h-1 bg-red-600"></div>

                        @if (isset($category) && $category?->term)
                            <p class="text-sm uppercase text-gray-700">
                                {{ $category->term->name }}
                            </p>
                        @endif

                        <h1 class="text-3xl md:text-[32px] font-sans text-[#0e4f7f]">
                            {{ $product->name }}
                        </h1>

                        <p class="text-gray-700 text-justify leading-relaxed">
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

                    {{-- Right Column (sticky image) --}}
                    @if ($media)
                        <div class="md:sticky md:top-4">
                            <div class="overflow-hidden" style="aspect-ratio:1/1;">
                                <a href="{{ $detailUrl }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="[
                                        150 => 'thumbnail',
                                        300 => 'medium',
                                        480 => 'mobile',
                                        768 => 'tablet',
                                        1024 => 'large',
                                    ]"
                                        sizes="(max-width:768px) 100vw, 50vw" width="1024" height="1024" loading="lazy"
                                        class="w-full h-full object-contain" alt="{{ $product->name }}" />
                                </a>
                            </div>
                        </div>
                    @else
                        <div>
                            <div class="w-full h-80 flex items-center justify-center text-gray-400">
                                —
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Page Content (if any) --}}
            <div class="prose max-w-none">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>

        </div>
    </div>
@endsection
