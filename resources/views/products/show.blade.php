@extends('layouts.app')

@section('content')
    <section class="bg-white py-12">
        <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">
            {{-- Product Image --}}
            <div class="relative">
                <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}"
                    class="w-full object-contain border rounded">
                <div class="absolute top-3 right-3 bg-white text-black text-xs px-2 py-1 rounded shadow">
                    ZOOM
                </div>
            </div>

            {{-- Product Info --}}
            <div>
                {{-- List all assigned categories (taxonomy terms) --}}
                @if ($product->taxonomies->isNotEmpty())
                    <h4 class="text-sm text-gray-500">
                        {{ $product->taxonomies->pluck('term.name')->join(', ') }}
                    </h4>
                @endif

                <h1 class="text-3xl font-bold uppercase">{{ $product->name }}</h1>

                <div class="mt-4 space-y-2 text-gray-700">
                    <h2 class="font-bold text-lg">Details:</h2>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach (explode("\n", $product->description) as $line)
                            @if (trim($line))
                                <li>{{ $line }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="mt-6 text-sm text-gray-500 space-y-1">
                    <p><strong>SKU:</strong> {{ $product->sku ?? 'N/A' }}</p>
                    <p><strong>Categories:</strong>
                        {{ $product->taxonomies->pluck('term.name')->join(', ') ?: '-' }}
                    </p>
                </div>

                <div class="mt-6">
                    <a href="#enquiry" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 text-sm">
                        ENQUIRY!
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Related Products --}}
    @if (isset($related) && $related->count())
        <section class="bg-white py-12 border-t">
            <div class="max-w-6xl mx-auto px-4">
                <h2 class="text-2xl font-bold mb-6">Related Products</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($related as $item)
                        <div class="text-center group">
                            <a href="{{ route('products.show', $item->slug) }}">
                                <img src="{{ asset('storage/' . $item->featured_image) }}" alt="{{ $item->name }}"
                                    class="w-full h-60 object-cover border border-gray-200 rounded shadow-sm 
                                            group-hover:scale-105 transition duration-300">
                            </a>

                            <h3 class="mt-3 text-sm font-bold uppercase">{{ $item->name }}</h3>
                            <div class="text-xs text-yellow-400 mt-1">☆☆☆☆☆</div>

                            <a href="{{ route('products.show', $item->slug) }}"
                                class="mt-2 inline-block bg-black text-white px-4 py-2 text-sm font-semibold rounded 
                                      hover:bg-gray-800 transition">
                                READ MORE
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
