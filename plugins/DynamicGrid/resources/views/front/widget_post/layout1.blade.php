@php
    use Illuminate\Support\Str;

    // Decide which model & relation to use
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Fetch all items in the selected taxonomy/category
    $items = $modelClass
        ::whereHas($relation, function ($q) use ($opts) {
            $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
        })
        ->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” &amp; category ID {{ $opts['category_id'] }}.
    </div>
@else
    <div
        class="
            dynamic-grid widget-layout1
            grid
            grid-cols-{{ $opts['columns']['mobile'] }}
            sm:grid-cols-{{ $opts['columns']['tablet'] }}
            md:grid-cols-{{ $opts['columns']['medium'] }}
            lg:grid-cols-{{ $opts['columns']['desktop'] }}
            xl:grid-cols-{{ $opts['columns']['large'] }}
            gap-6
        ">
        @foreach ($items as $item)
            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                {{-- Image --}}
                @if ($opts['show_image'])
                    <img src="{{ optional($item->featuredMedia->first())->getUrl() }}"
                        alt="{{ $item->name ?? $item->title }}" class="w-full h-32 object-cover rounded mb-3">
                @endif

                {{-- Title --}}
                <h3 class="font-semibold text-lg">{{ $item->name ?? $item->title }}</h3>

                {{-- Excerpt --}}
                <p class="mt-2 text-gray-600 flex-1">
                    {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                </p>
            </div>
        @endforeach
    </div>
@endif
