{{-- resources/views/themes/classic/templates/product.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('themes.classic.layout')

@section('content')
    <div class="bg-white py-12 font-[oswald]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- IMAGE + ZOOM ICON --}}
                <div class="relative">
                    @php $img = $product->featured_image; @endphp
                    @if ($img && Storage::disk('public')->exists($img))
                        <img src="{{ Storage::url($img) }}" alt="{{ $product->name }}"
                            class="w-full h-64 sm:h-80 md:h-96 rounded-lg object-cover shadow" />
                    @else
                        <div
                            class="w-full h-64 sm:h-80 md:h-96 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    @endif
                </div>

                {{-- DETAILS --}}
                <div class="flex flex-col">
                    {{-- Breadcrumb --}}
                    <nav class="text-sm sm:text-base text-gray-500 mb-4" aria-label="Breadcrumb">
                        <ol class="list-reset flex flex-wrap space-x-2">
                            <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                            <li>/</li>
                            <li>
                                <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                    {{ strtoupper($category->name) }}
                                </a>
                            </li>
                            <li>/</li>
                            <li class="font-semibold">{{ strtoupper($product->name) }}</li>
                        </ol>
                    </nav>

                    {{-- Title --}}
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                        {{ strtoupper($product->name) }}
                    </h1>

                    {{-- Description --}}
                    <div class="mb-6">
                        <span class="font-semibold text-gray-700">Description</span>
                        <p class="mt-2 text-sm sm:text-base text-gray-800">{{ $product->description }}</p>
                    </div>

                    {{-- SKU & Category --}}
                    <div class="flex flex-col sm:flex-row sm:space-x-8 mb-8 text-gray-700">
                        <div><span class="font-semibold">SKU:</span> {{ $product->sku }}</div>
                        <div><span class="font-semibold">Category:</span> {{ strtoupper($category->name) }}</div>
                    </div>

                    {{-- Enquiry Button --}}
                    <a href="{{-- route('inquiry.create', ['product' => $product->slug]) --}}"
                        class="inline-block w-full sm:w-auto text-center px-4 sm:px-6 py-3 bg-black text-white font-semibold rounded shadow hover:bg-gray-800 transition">
                        ENQUIRE NOW
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
