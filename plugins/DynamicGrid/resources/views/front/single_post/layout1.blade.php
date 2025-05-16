@php
    use Illuminate\Support\Str;

    // Decide which model to query:
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;

    // And which relation to use:
    $relation = $isProductTax
        ? 'taxonomies' // on Product model
        : 'termTaxonomies'; // on Post model

    // Fetch items in the chosen taxonomy/category
    $items = $modelClass
        ::whereHas($relation, function ($q) use ($opts) {
            $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
        })
        ->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” &amp; category {{ $opts['category_id'] }}.
    </div>
@else
    <div class="dynamic-grid single-layout1 space-y-6">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-2xl font-bold">{{ $opts['heading'] }}</h2>
        @endif

        <div
            class="
                grid
                grid-cols-{{ $opts['columns']['mobile'] }}
                sm:grid-cols-{{ $opts['columns']['tablet'] }}
                md:grid-cols-{{ $opts['columns']['medium'] }}
                lg:grid-cols-{{ $opts['columns']['desktop'] }}
                xl:grid-cols-{{ $opts['columns']['large'] }}
                gap-6
            ">
            @foreach ($items as $item)
                <div class="bg-white rounded-lg p-4  flex flex-col items-center text-center">
                    {{-- Clickable Responsive Image --}}
                    @if ($opts['show_image'] && ($media = $item->featuredMedia->first()))
                        <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}">
                            <x-responsive-image :media="$media" class="w-full h-auto object-cover rounded mb-4" />
                        </a>
                    @endif

                    {{-- Title --}}
                    <h3 class="font-medium text-lg leading-tight my-2 ">
                        {{ $isProductTax ? $item->name : $item->title }}
                    </h3>

                    {{-- Excerpt (optional) --}}
                    {{--   @if ($opts['excerpt_words'] > 0)
                        <p class="text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                        </p>
                    @endif --}}

                    {{-- Underlined Button --}}
                    @if ($opts['button_type'] === 'price')
                        <button
                            class="mt-auto px-4 pb-1 text-blue-600 font-medium
                                   border-b-2 border-blue-600 hover:text-blue-800">
                            Get Price
                        </button>
                    @elseif($opts['button_type'] === 'read_more')
                        <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}"
                            class="mt-auto px-4 pb-1 text-blue-600 font-medium
                             border-b-2 border-blue-600 hover:text-blue-800">
                            Read More
                        </a>
                    @endif
                </div>
            @endforeach


        </div>
    </div>
@endif
