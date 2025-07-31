@php
    $count = $products->count();
    // if you have 4 or more, show max 4 columns; otherwise show exactly as many columns as you have products
    $cols = $count >= 4 ? 4 : ($count ?: 1);
    // responsive columns: 1 on mobile, up to $cols on lg
    $sm = $cols >= 2 ? 2 : $cols;
    $md = $cols >= 3 ? 3 : $cols;
    $lg = $cols;
@endphp

@if ($products->isNotEmpty())
    <section>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 font-[oswald]">
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl mb-8 text-center uppercase">
                Featured Products
            </h2>

            <div
                class="grid grid-cols-1 sm:grid-cols-{{ $sm }} md:grid-cols-{{ $md }} lg:grid-cols-{{ $lg }} gap-6">
                @foreach ($products as $p)
                    <a href="{{ route('products.show', $p->slug) }}"
                        class="group block bg-white rounded-lg overflow-hidden  hover:shadow-lg transition">
                        @php $img = $p->featured_image; @endphp
                        @if ($img && Storage::disk('public')->exists($img))
                            <img src="{{ Storage::url($img) }}" alt="{{ $p->name }}"
                                class="w-full h-40 sm:h-48 md:h-56 lg:h-60 object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <div
                                class="w-full h-40 sm:h-48 md:h-56 lg:h-60 bg-gray-100 flex items-center justify-center text-gray-400">
                                No Image
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="text-base sm:text-lg font-semibold mb-2 uppercase text-center">
                                {{ $p->name }}
                            </h3>
                            <div class="text-center">
                                <span
                                    class="inline-block px-3 py-1 sm:px-4 sm:py-2 bg-gray-900 text-white text-sm sm:text-base font-medium rounded">
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
