{{-- resources/views/themes/classic/templates/product.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('themes.classic.layout')

@section('content')
    <div class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2  lg:grid-cols-2 gap-8">
                {{-- IMAGE + ZOOM ICON --}}
                <div class="relative">
                    @php $img = $product->featured_image; @endphp
                    @if ($img && Storage::disk('public')->exists($img))
                        <img src="{{ Storage::url($img) }}" alt="{{ $product->name }}"
                            class="w-full rounded-lg object-cover shadow" />
                    @else
                        <div class="w-full h-96 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    @endif


                </div>

                {{-- DETAILS --}}
                <div class="flex flex-col">
                    {{-- Breadcrumb --}}
                    <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                        <ol class="list-reset flex space-x-2">
                            <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                            <li>/</li>
                            <li><a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                    {{ strtoupper($category->name) }}
                                </a></li>
                            <li>/</li>
                            <li class="font-semibold">{{ strtoupper($product->name) }}</li>
                        </ol>
                    </nav>

                    {{-- Title --}}
                    <h1 class="text-4xl font-bold mb-4">{{ strtoupper($product->name) }}</h1>

                    {{-- Description --}}
                    <div class="mb-6">
                        <span class="font-semibold text-gray-700">Description</span>
                        <p class="mt-2 text-gray-800">{{ $product->description }}</p>
                    </div>

                    {{-- SKU & Category --}}
                    <div class="flex space-x-8 mb-8 text-gray-700">
                        <div><span class="font-semibold">SKU:</span> {{ $product->sku }}</div>
                        <div><span class="font-semibold">Category:</span> {{ strtoupper($category->name) }}</div>
                    </div>

                    {{-- Enquiry Button --}}
                    <a href="{{-- {{ route('inquiry.create', ['product' => $product->slug]) }} --}}"
                        class="inline-block px-6 py-3 bg-black text-white font-semibold rounded shadow hover:bg-gray-800 transition">
                        ENQUIRY!
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
