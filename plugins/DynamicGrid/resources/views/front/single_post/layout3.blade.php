{{-- resources/views/plugins/DynamicGrid/templates/products/layout3.blade.php --}}
@php
    use Illuminate\Support\Arr;

    // ----- Columns (safe defaults) -----
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

    // ----- Model/relations -----
    $isProductTax = ($opts['taxonomy'] ?? 'product') === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // ----- Query with qualified columns to avoid ambiguity -----
    $q = $modelClass::query()->with([$relation, 'featuredMedia']);

    if (!empty($opts['category_id'])) {
        $q->whereHas($relation, function ($qq) use ($opts) {
            $qq->where('term_taxonomies.term_taxonomy_id', $opts['category_id']);
            if (!empty($opts['taxonomy'])) {
                $qq->where('term_taxonomies.taxonomy', $opts['taxonomy']);
                $qq->where('term_relationships.object_type', $opts['taxonomy']); // e.g. 'product'
            }
        });
    }

    if (!empty($opts['product_amount'])) {
        $q->take((int) $opts['product_amount']);
    }

    $items = $q->latest()->get();

    // ----- Responsive image settings (same as layout1) -----
    $breakpoints = [
        150 => 'thumbnail',
        300 => 'medium',
        480 => 'mobile',
        768 => 'tablet',
        1024 => 'large',
    ];
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

    // ----- Static lines -----
    $staticPrice = 'USD/PC: 2.00 - 4.10';
    $staticMoq = 'MOQ/Colour: 500 Pcs';

    // ----- Show image by default -----
    $showImage = array_key_exists('show_image', $opts) ? (bool) $opts['show_image'] : true;

    // ----- Helpers -----
    $titleOf = function ($item) use ($isProductTax) {
        return $isProductTax
            ? $item->name ?? ($item->title ?? 'Untitled')
            : $item->title ?? ($item->name ?? 'Untitled');
    };
    $styleOf = fn($item) => $item->style ?? ($item->sku ?? '—');
    $urlOf = function ($item) use ($isProductTax) {
        try {
            if ($isProductTax) {
                return route('products.show', $item->slug ?? $item->id);
            }
            return route('posts.show', $item->slug ?? $item->id);
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
    <div class="relative products-layout3 !ml-0">
        @if (!empty($opts['heading']))
            <h2 class="text-4xl font-bold text-center mb-14 mt-8">{{ $opts['heading'] }}</h2>
        @endif

        <div
            class="grid
                   grid-cols-{{ (int) $cols['mobile'] }}
                   sm:grid-cols-{{ (int) $cols['tablet'] }}
                   md:grid-cols-{{ (int) $cols['medium'] }}
                   lg:grid-cols-{{ (int) $cols['desktop'] }}
                   xl:grid-cols-{{ (int) $cols['large'] }}
                   gap-8">

            @foreach ($items as $item)
                @php
                    $media = method_exists($item, 'featuredMedia') ? $item->featuredMedia->first() : null;
                    $title = $titleOf($item);
                    $style = $styleOf($item);
                    $url = $urlOf($item);
                @endphp

                {{-- Card --}}
                <article
                    class="product-card bg-white rounded-lg shadow-[0_1px_3px_rgba(0,0,0,0.1),0_1px_2px_rgba(0,0,0,0.06)] hover:shadow-[0_10px_15px_rgba(0,0,0,0.1),0_4px_6px_rgba(0,0,0,0.06)] overflow-hidden transition">

                    @if ($showImage)
                        <a href="{{ $url }}" class="block">
                            <div class="w-full overflow-hidden product-image-wrap" style="aspect-ratio:4/3;">
                                @if ($media)
                                    <x-responsive-image :media="$media" :breakpoints="$breakpoints" sizes="{{ $sizes }}"
                                        width="640" height="480" loading="lazy"
                                        class="w-full h-full object-cover product-image" alt="{{ $title }}" />
                                @endif
                            </div>
                        </a>
                    @endif

                    <div class="p-6 flex flex-col product-info">
                        <div class="text-sm text-gray-600 mb-2 product-code">
                            SHK: {{ $style }}
                        </div>

                        <h3 class="text-lg leading-snug mb-3 min-h-[2.5rem] product-title">
                            <a href="{{ $url }}" class="hover:underline">{{ $title }}</a>
                        </h3>

                        <div class="font-semibold mb-2 product-price">
                            <span class="text-red-600">{{ $staticPrice }}</span>
                        </div>

                        <div class="text-sm text-gray-700 product-moq">
                            {{ $staticMoq }}
                        </div>

                        @if (($opts['button_type'] ?? '') === 'price')
                            <button type="button"
                                class="get-price-btn mt-4 px-4 py-2 text-blue-600 font-medium border-b-2 border-blue-600 hover:text-blue-800 text-sm md:text-base"
                                data-id="{{ $item->id }}" data-title="{{ e($title) }}"
                                data-image="{{ optional($media)->getUrl('thumbnail') }}"
                                data-url="{{ $url }}">
                                Get Price
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endif
