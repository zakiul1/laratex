@php
    use Illuminate\Support\Str;

    // Determine model & relation (for product vs post taxonomy)
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax
        ? 'taxonomies' // Product::taxonomies()
        : 'termTaxonomies'; // Post::termTaxonomies()

    // Fetch items
    $items = $modelClass
        ::whereHas($relation, function ($q) use ($opts) {
            $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
        })
        ->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” and category ID {{ $opts['category_id'] }}.
    </div>
@else
    <div class="dynamic-grid single-layout2 space-y-6">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-2xl font-bold">{{ $opts['heading'] }}</h2>
        @endif

        <div class="space-y-8">
            @foreach ($items as $item)
                <div class="flex flex-col md:flex-row  bg-white rounded-lg overflow-hidden">
                    {{-- Image --}}
                    @if ($opts['show_image'] && ($media = $item->featuredMedia->first()))
                        <div class="md:w-1/3">
                            <x-responsive-image :media="$media" alt="{{ $isProductTax ? $item->name : $item->title }}"
                                class="w-full h-full " />
                        </div>
                    @endif

                    {{-- Text --}}
                    <div class=" flex-1">
                        <h3 class="text-xl font-semibold mb-2">
                            {{ $isProductTax ? $item->name : $item->title }}
                        </h3>

                        <p class="text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                        </p>

                        @if ($opts['button_type'] === 'price')
                            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                                Get Price
                            </button>
                        @elseif ($opts['button_type'] === 'read_more')
                            <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}"
                                class="inline-block px-4 py-2 border border-blue-600 text-blue-600 rounded">
                                Read More
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
