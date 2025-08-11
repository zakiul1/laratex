{{-- resources/views/plugins/DynamicGrid/templates/products/layout3.blade.php --}}
@php
    // Column defaults
    $cols = array_merge(
        [
            'mobile' => 1,
            'tablet' => 2,
            'medium' => 3,
            'desktop' => 4,
            'large' => 4,
        ],
        $opts['columns'] ?? [],
    );

    // Models / relations
    $isProductTax = ($opts['taxonomy'] ?? 'product') === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Query (qualify columns to avoid ambiguity)
    $q = $modelClass::query()->with([$relation, 'featuredMedia']);
    if (!empty($opts['category_id'])) {
        $q->whereHas($relation, function ($qq) use ($opts) {
            $qq->where('term_taxonomies.term_taxonomy_id', $opts['category_id']);
            if (!empty($opts['taxonomy'])) {
                $qq->where('term_taxonomies.taxonomy', $opts['taxonomy']);
                $qq->where('term_relationships.object_type', $opts['taxonomy']); // e.g., 'product'
            }
        });
    }
    if (!empty($opts['product_amount'])) {
        $q->take((int) $opts['product_amount']);
    }
    $items = $q->latest()->get();

    // Responsive image (same approach as layout1)
    $breakpoints = [150 => 'thumbnail', 300 => 'medium', 480 => 'mobile', 768 => 'tablet', 1024 => 'large'];
    $sizes =
        '(max-width: 640px) 100vw, ' .
        '(max-width: 768px) ' .
        round(100 / max(1, $cols['tablet']), 2) .
        'vw, ' .
        '(max-width: 1024px) ' .
        round(100 / max(1, $cols['medium']), 2) .
        'vw, ' .
        '(max-width: 1280px) ' .
        round(100 / max(1, $cols['desktop']), 2) .
        'vw, ' .
        round(100 / max(1, $cols['large']), 2) .
        'vw';

    // Static lines
    $staticPrice = 'USD/PC: 2.00 - 4.10';
    $staticMoq = 'MOQ/Colour: 500 Pcs';

    // Show image unless explicitly disabled
    $showImage = array_key_exists('show_image', $opts) ? (bool) $opts['show_image'] : true;

    // Helpers
    $titleOf = fn($it) => $isProductTax
        ? $it->name ?? ($it->title ?? 'Untitled')
        : $it->title ?? ($it->name ?? 'Untitled');
    $styleOf = fn($it) => $it->style ?? ($it->sku ?? '—');
    $urlOf = function ($it) use ($isProductTax) {
        try {
            return $isProductTax
                ? route('products.show', $it->slug ?? $it->id)
                : route('posts.show', $it->slug ?? $it->id);
        } catch (\Throwable $e) {
            return '#';
        }
    };
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800">
        No items found for “{{ $opts['taxonomy'] ?? 'product' }}” & category {{ $opts['category_id'] ?? '—' }}.
    </div>
@else
    @if (!empty($opts['heading']))
        <h2 class="text-[2.5rem] leading-tight text-center font-light text-neutral-900 mb-6">
            {{ $opts['heading'] }}
        </h2>
    @endif

    <div
        class="grid gap-[30px]
               grid-cols-{{ (int) $cols['mobile'] }}
               sm:grid-cols-{{ (int) $cols['tablet'] }}
               md:grid-cols-{{ (int) $cols['medium'] }}
               lg:grid-cols-{{ (int) $cols['desktop'] }}
               xl:grid-cols-{{ (int) $cols['large'] }}">

        @foreach ($items as $item)
            @php
                $media = method_exists($item, 'featuredMedia') ? $item->featuredMedia->first() : null;
                $title = $titleOf($item);
                $style = $styleOf($item);
                $url = $urlOf($item);
            @endphp

            <!-- Product Card -->
            <article
                class="product-card bg-white rounded-[8px] overflow-hidden
                       shadow-[0_3px_10px_rgba(0,0,0,0.10)]
                       hover:-translate-y-[5px]
                       transition-transform duration-300 ease-out">

                @if ($showImage)
                    <a href="{{ $url }}" class="block">
                        <div class="w-full h-[280px] overflow-hidden">
                            @if ($media)
                                <x-responsive-image :media="$media" :breakpoints="$breakpoints" sizes="{{ $sizes }}"
                                    width="640" height="480" loading="lazy"
                                    class="product-image w-full h-full object-cover" alt="{{ $title }}" />
                            @else
                                <img class="product-image w-full h-full object-cover"
                                    src="https://via.placeholder.com/640x480?text=No+Image" alt="{{ $title }}">
                            @endif
                        </div>
                    </a>
                @endif

                <div class="product-info p-5">
                    <div class="product-code text-[#666] text-[0.9rem] mb-2">
                        SHK: {{ $style }}
                    </div>

                    <h3 class="product-title text-[1.2rem] font-medium leading-snug mb-2 text-[#222]">
                        <a href="{{ $url }}" class="hover:underline">{{ $title }}</a>
                    </h3>

                    <div class="product-price font-bold text-[#e74c3c] my-2">
                        {{ $staticPrice }}
                    </div>

                    <div class="product-moq text-[#666] text-[0.9rem]">
                        {{ $staticMoq }}
                    </div>

                    {{-- Add to cart (light color + light border; navigates to product view) --}}
                    <a href="{{ $url }}"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-full
                              border border-gray-300 bg-white text-gray-800
                              px-6 py-2.5 text-sm font-semibold
                              hover:bg-gray-50 active:bg-gray-100
                              focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 focus-visible:ring-offset-2">
                        Add to cart
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif
