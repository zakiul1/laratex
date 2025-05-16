@php
    use Illuminate\Support\Str;

    // Determine model & relation
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax
        ? 'taxonomies' // Product::taxonomies()
        : 'termTaxonomies'; // Post::termTaxonomies()

    // Main featured item
    $main = $modelClass::find($opts['post_id'] ?? null);

    // All items in this taxonomy/category
    $items = $modelClass
        ::whereHas($relation, function ($q) use ($opts) {
            $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
        })
        ->get();
@endphp

@if (!$main)
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No valid item found for ID {{ $opts['post_id'] ?? '(none)' }}.
    </div>
@else
    <div class="dynamic-grid feature-layout2 space-y-6">
        {{-- Heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-2xl font-bold">{{ $opts['heading'] }}</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Featured main item --}}
            <div class="bg-white rounded shadow p-6 flex flex-col">
                @if ($opts['show_image'])
                    <img src="{{ optional($main->featuredMedia->first())->getUrl() }}"
                        alt="{{ $main->name ?? $main->title }}" class="w-full h-48 object-cover rounded mb-4">
                @endif

                <p class="flex-1 text-gray-700">
                    {{ Str::words(strip_tags($main->description ?? $main->content), $opts['excerpt_words'], '…') }}
                </p>

                @if ($opts['button_type'] === 'price')
                    <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                        Get Price
                    </button>
                @elseif ($opts['button_type'] === 'read_more')
                    <a href="{{ $isProductTax ? route('products.show', $main) : route('posts.show', $main) }}"
                        class="mt-4 inline-block px-4 py-2 border border-blue-600 text-blue-600 rounded">
                        Read More
                    </a>
                @endif
            </div>

            {{-- Other items --}}
            @foreach ($items as $item)
                <div class="bg-white rounded shadow p-6 flex flex-col">
                    @if ($opts['show_image'])
                        <img src="{{ optional($item->featuredMedia->first())->getUrl() }}"
                            alt="{{ $item->name ?? $item->title }}" class="w-full h-32 object-cover rounded mb-4">
                    @endif

                    <h3 class="font-semibold text-lg">
                        {{ $item->name ?? $item->title }}
                    </h3>

                    <p class="mt-2 flex-1 text-gray-600">
                        {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                    </p>

                    <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}"
                        class="mt-4 text-sm text-blue-600">
                        Read More
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
