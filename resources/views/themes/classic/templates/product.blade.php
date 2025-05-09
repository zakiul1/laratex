@extends('themes.classic.layout')

@section('content')
    <div class="bg-white py-12 font-[oswald]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            {{-- PRODUCT DETAIL GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- IMAGE --}}
                <div class="relative">
                    @php $media = $product->featuredMedia->first(); @endphp

                    @if ($media)
                        <div class="w-full aspect-w-16 aspect-h-9 rounded-lg overflow-hidden shadow p-4">
                            <x-responsive-image :media="$media" class="w-full h-full object-cover"
                                alt="{{ $product->name }}" />
                        </div>
                    @else
                        <div
                            class="w-full h-64 sm:h-80 md:h-96 bg-gray-100 
                   rounded-lg flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    @endif
                </div>


                {{-- DETAILS --}}
                <div class="flex flex-col">
                    {{-- Breadcrumb --}}
                    <nav class="text-sm sm:text-base text-gray-500 mb-4" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap space-x-2">
                            <li>
                                <a href="{{ route('home') }}" class="hover:underline">Home</a>
                            </li>
                            <li>/</li>
                            @if ($category)
                                <li>
                                    <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                        {{ strtoupper($category->name) }}
                                    </a>
                                </li>
                                <li>/</li>
                            @endif
                            <li class="font-semibold">{{ strtoupper($product->name) }}</li>
                        </ol>
                    </nav>

                    {{-- Title --}}
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                        {{ strtoupper($product->name) }}
                    </h1>

                    {{-- Description --}}
                    <div class="mb-6">
                        <span class="font-semibold text-gray-700">Details:</span>

                        @if (!empty($product->description))
                            @php
                                // split by newlines (handles \r\n or \n)
                                $items = preg_split('/\r\n|\r|\n/', trim($product->description));
                            @endphp

                            <ul class="list-disc list-inside mt-2 text-sm text-gray-800 space-y-1">
                                @foreach ($items as $item)
                                    @if (trim($item) !== '')
                                        <li>{{ $item }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-2 text-sm text-gray-800">No description available.</p>
                        @endif
                    </div>


                    {{-- SKU & Category --}}
                    <div class="flex flex-col sm:flex-row sm:space-x-8 mb-8 text-gray-700">
                        @if (!empty($product->sku))
                            <div><span class="font-semibold">SKU:</span> {{ $product->sku }}</div>
                        @endif
                        @if ($category)
                            <div>
                                <span class="font-semibold">Category:</span>
                                {{ strtoupper($category->name) }}
                            </div>
                        @endif
                    </div>

                    {{-- Enquiry Button --}}
                    <a href="#"
                        class="inline-block w-full sm:w-auto text-center px-4 sm:px-6 py-3 
                               bg-black text-white font-semibold rounded shadow hover:bg-gray-800 transition">
                        ENQUIRE NOW
                    </a>
                </div>
            </div>

            {{-- FEATURED PRODUCTS --}}
            @if (!empty($featuredProducts) && $featuredProducts->isNotEmpty())
                <div class="mt-16">
                    <h2 class="text-3xl font-bold mb-6">
                        {{ $featuredCategory->term->name }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($featuredProducts as $fp)
                            @php $fm = $fp->featuredMedia->first(); @endphp

                            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                                <a href="{{ route('products.show', $fp->slug) }}" class="flex-1">
                                    @if ($fm)
                                        <div class="w-full aspect-w-16 aspect-h-9 rounded-lg overflow-hidden  mb-4">
                                            <x-responsive-image :media="$fm" class="w-full h-full object-cover"
                                                alt="{{ $fp->name }}" />
                                        </div>
                                    @else
                                        <div
                                            class="w-full h-48 bg-gray-100 rounded mb-4 flex items-center justify-center text-gray-400">
                                            No Image
                                        </div>
                                    @endif

                                    <h3 class="text-xl text-center font-semibold mb-2">{{ $fp->name }}</h3>
                                    <p class="text-gray-600 mb-4">
                                        {{ \Illuminate\Support\Str::limit($fp->description ?? '', 80) }}
                                    </p>
                                </a>

                                @if (!is_null($fp->price))
                                    <div class="mt-auto">
                                        <span class="text-lg font-bold">৳{{ number_format($fp->price, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>
            @endif

        </div>
    </div>
@endsection
