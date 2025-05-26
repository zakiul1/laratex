{{-- resources/views/plugins/DynamicGrid/templates/services_overview.blade.php --}}
@php
    use Illuminate\Support\Str;
    use App\Models\Post;
    use App\Models\Product;
    use App\Helpers\BlockRenderer;

    // Determine which model to use
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? Product::class : Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Fetch items, optionally limiting by product_amount
    $query = $modelClass::whereHas(
        $relation,
        fn($q) => $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']),
    );
    if (!empty($opts['product_amount'])) {
        $query->take(intval($opts['product_amount']));
    }
    $items = $query->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found.
    </div>
@else
    <div class="space-y-12">
        @foreach ($items as $item)
            @php
                $media = $item->featuredMedia->first();
                $title = $isProductTax ? $item->name : $item->title;
                $excerpt = Str::words(
                    strip_tags(BlockRenderer::render($item->description ?? $item->content)),
                    3000,
                    '…',
                );
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                @if ($media)
                    <div class="md:col-span-1">
                        <x-responsive-image :media="$media" alt="{{ $title }}"
                            class="w-full h-auto object-cover rounded" />
                    </div>
                @endif

                <div class="md:col-span-2 space-y-2">
                    <h3 class="text-xl font-semibold text-blue-800">{{ $title }}</h3>
                    <p class="text-gray-700">{{ $excerpt }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endif
