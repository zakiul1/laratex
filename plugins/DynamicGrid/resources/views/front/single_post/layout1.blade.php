{{-- resources/views/plugins/DynamicGrid/templates/single_layout1.blade.php --}}
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
        $query->take(intval($opts['product_amount']));
    }

    $items = $query->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” & category {{ $opts['category_id'] }}.
    </div>
@else
    <div class="relative single-layout1 !ml-0">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-4xl font-bold text-center mb-6">
                {{ $opts['heading'] }}
            </h2>
        @endif

        {{-- Grid --}}
        <div
            class="grid
                 grid-cols-{{ $opts['columns']['mobile'] }}
                 sm:grid-cols-{{ $opts['columns']['tablet'] }}
                 md:grid-cols-{{ $opts['columns']['medium'] }}
                 lg:grid-cols-{{ $opts['columns']['desktop'] }}
                 xl:grid-cols-{{ $opts['columns']['large'] }}
                 gap-6">
            @foreach ($items as $item)
                @php
                    $media = $item->featuredMedia->first();
                    $title = $isProductTax ? $item->name : $item->title;
                    $url = $isProductTax ? route('products.show', $item->slug) : route('posts.show', $item->slug);
                @endphp

                <div class="bg-white rounded-lg flex flex-col items-center text-center p-4">
                    @if (!empty($opts['show_image']) && $media)
                        {{-- 1. aspect-ratio container (4:3) --}}
                        <div class="w-full mb-4 overflow-hidden" style="aspect-ratio:4/3;">
                            <a href="{{ $url }}" class="block w-full h-full">
                                {{-- 2. explicit width/height --}}
                                <x-responsive-image :media="$media" alt="{{ $title }}"
                                    class="w-full h-full object-cover" loading="lazy" width="400" height="300" />
                            </a>
                        </div>
                    @endif

                    <h3 class="font-medium text-lg my-2">{{ $title }}</h3>

                    @if (!empty($opts['show_description']) && !empty($opts['excerpt_words']))
                        <p class="text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                        </p>
                    @endif

                    @if (($opts['button_type'] ?? '') === 'price')
                        <button type="button"
                            class="get-price-btn mt-auto px-4 py-2 text-blue-600 font-medium
                                 border-b-2 border-blue-600 hover:text-blue-800"
                            data-id="{{ $item->id }}" data-title="{{ e($title) }}"
                            data-image="{{ $media ? $media->getUrl('thumbnail') : '' }}"
                            data-url="{{ $url }}">
                            Get Price
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
