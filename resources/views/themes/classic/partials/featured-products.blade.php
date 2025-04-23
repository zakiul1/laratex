@php
    $count = $products->count();
    // if you have 4 or more, show 4 columns; otherwise show exactly as many columns as you have products
    $cols = $count >= 4 ? 4 : ($count ?: 1);
@endphp

@if ($products->isNotEmpty())
    <section>
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold mb-8 text-center uppercase">
                Featured Products
            </h2>


            <div class="grid gap-6" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
                @foreach ($products as $p)
                    <a href="{{ route('products.show', $p->slug) }}"
                        class="group block bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                        @php $img = $p->featured_image; @endphp
                        @if ($img && Storage::disk('public')->exists($img))
                            <img src="{{ Storage::url($img) }}" alt="{{ $p->name }}"
                                class="w-full h-60 object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                                No Image
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="text-center text-sm font-semibold mb-2 uppercase">{{ $p->name }}</h3>
                            <div class="text-center">
                                <span class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded">
                                    Read More
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
