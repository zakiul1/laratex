{{-- resources/views/themes/siatexbd/templates/product.blade.php --}}
@extends('themes.siatex-global.layout')

@section('content')
    @php
        use Illuminate\Support\Str;

        $media = $product->featuredMedia->first();
        $mediaUrl = $media ? $media->getUrl('large') : '';
        $detailUrl = route('products.show', $product->slug ?? $product->id);
    @endphp

    <div class="bg-white py-12 relative">
        @auth
            <div class="absolute top-4 right-4">
                <a href="{{ route('products.edit', $product) }}" class="text-xs px-3 py-3 rounded bg-green-500 text-white">
                    Edit Product
                </a>
            </div>
        @endauth

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-700" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home') }}" class="hover:underline text-gray-700">Home</a>
                    </li>
                    <li>/</li>

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
            <div class="bg-gray-100 p-6 md:p-12 rounded">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    {{-- Left: details --}}
                    <div class="space-y-4">
                        <div class="w-16 h-1 bg-red-600"></div>

                        @if (isset($category) && $category?->term)
                            <h2 class="font-medium text-lg my-2 uppercase text-gray-700">
                                {{ $category->term->name }}
                            </h2>
                        @endif

                        <h2 class="text-3xl md:text-[32px] font-sans text-[#0e4f7f]">
                            {{ $product->name }}
                        </h2>

                        <p class="text-gray-700 text-justify leading-relaxed">
                            @if (!empty($product->excerpt))
                                {!! nl2br(e($product->excerpt)) !!}
                            @elseif (!empty($product->description))
                                {!! nl2br(e(Str::limit(strip_tags($product->description), 3000, '…'))) !!}
                            @else
                                No description available.
                            @endif
                        </p>

                        {{-- Start order opens the drawer --}}
                        <button type="button" x-data @click="$dispatch('open-order-drawer')"
                            class="bg-blue-800 hover:bg-blue-900 text-white px-6 py-3 rounded-md">
                            Start order
                        </button>

                    </div>

                    {{-- Right: image --}}
                    <div>
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
                                            sizes="(max-width:768px) 100vw, 50vw" width="1024" height="1024"
                                            loading="lazy" class="w-full h-full object-contain"
                                            alt="{{ $product->name }}" />
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="w-full h-80 flex items-center justify-center text-gray-400">—</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Page content (shortcodes/hooks) --}}
            <div class="prose max-w-none">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>
        </div>
    </div>

    {{-- Right-side order drawer (make sure this file exists at the path below) --}}
    @include('themes.siatex-global.partials.order-drawer', ['product' => $product])
@endsection
