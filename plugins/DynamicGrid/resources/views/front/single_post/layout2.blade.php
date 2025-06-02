{{-- resources/views/plugins/DynamicGrid/templates/single_layout2.blade.php --}}

@php
    use Illuminate\Support\Str;

    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Build base query
    $query = $modelClass::whereHas($relation, function ($q) use ($opts) {
        $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
    });

    if (($opts['type'] ?? '') === 'single_post' && !empty($opts['product_amount'])) {
        $query->take((int) $opts['product_amount']);
    }

    $items = $query->get();

    // ── 1) Revised “breakpoints” to include mobile (480px) and tablet (768px) ──
    $breakpoints = [
        150 => 'thumbnail',
        300 => 'medium',
        480 => 'mobile', // 480px‐wide version for phones
        768 => 'tablet', // 768px‐wide version for small tablets
        1024 => 'large',
    ];
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800">
        No items found for “{{ $opts['taxonomy'] }}” & category {{ $opts['category_id'] }}.
    </div>
@else
    <div class="space-y-8 !ml-0">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-3xl font-bold text-center mb-2">
                {{ $opts['heading'] }}
            </h2>
            @if (!empty($opts['subheading']))
                <p class="text-gray-600 text-center max-w-2xl mx-auto mb-6">
                    {{ $opts['subheading'] }}
                </p>
            @endif
        @endif

        {{-- Single column on mobile (≤768px); two columns at md (≥768px) --}}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            @foreach ($items as $item)
                @php
                    $media = $item->featuredMedia->first();
                    $title = $isProductTax ? $item->name : $item->title;
                    $excerpt = Str::words(
                        strip_tags($item->description ?? $item->content),
                        $opts['excerpt_words'] ?? 30,
                        '…',
                    );
                    $url = $isProductTax ? route('products.show', $item->slug) : route('posts.show', $item->slug);
                @endphp

                <div class="flex flex-col md:flex-row md:space-x-6">
                    {{-- Image (square aspect ratio) --}}
                    <div class="w-full md:w-1/3 flex-shrink-0 mb-4 md:mb-0">
                        @if (!empty($opts['show_image']) && $media)
                            <div class="overflow-hidden" style="aspect-ratio:1/1;">
                                <a href="{{ $url }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="$breakpoints" {{-- 2) sizes: ≤768px → 100vw; >768px → 50vw --}}
                                        sizes="(max-width: 768px) 100vw, 50vw" width="400" height="400"
                                        loading="lazy" class="w-full h-full object-contain" alt="{{ $title }}" />
                                </a>
                            </div>
                        @else
                            <div class="overflow-hidden bg-gray-100 flex items-center justify-center text-gray-400"
                                style="aspect-ratio:1/1;">
                                —
                            </div>
                        @endif
                    </div>

                    {{-- Text --}}
                    <div class="w-full md:w-2/3 space-y-2">
                        <h3 class="text-xl font-semibold text-blue-800">
                            <a href="{{ $url }}" class="hover:text-blue-600 transition">
                                {{ $title }}
                            </a>
                        </h3>

                        @if (!empty($opts['show_description']))
                            <p class="text-gray-600 leading-relaxed">{{ $excerpt }}</p>
                        @endif

                        <a href="{{ $url }}"
                            class="inline-flex items-center text-blue-600 font-medium hover:underline">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        @if (($opts['button_type'] ?? '') === 'price')
                            <button type="button"
                                class="get-price-btn mt-4 px-4 py-2 text-blue-600 font-medium
                                       border-b-2 border-blue-600 hover:text-blue-800"
                                data-id="{{ $item->id }}" data-title="{{ e($title) }}"
                                data-image="{{ $media->getUrl('thumbnail') }}" data-url="{{ $url }}">
                                Get Price
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
