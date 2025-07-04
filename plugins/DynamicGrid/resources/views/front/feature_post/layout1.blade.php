{{-- resources/views/plugins/DynamicGrid/templates/services_overview.blade.php --}}

@php
    use Illuminate\Support\Str;
    use App\Models\Post;
    use App\Models\Product;
    use App\Helpers\BlockRenderer;

    // Determine model & relation
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? Product::class : Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Fetch items, optionally limiting by product_amount
    $query = $modelClass::whereHas(
        $relation,
        fn($q) => $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']),
    );

    if (!empty($opts['product_amount'])) {
        $query->take((int) $opts['product_amount']);
    }

    $items = $query->get();
    $breakpoints = [150 => 'thumbnail', 300 => 'medium', 1024 => 'large'];
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found.
    </div>
@else
    {{-- Optional Heading --}}
    @if (!empty($opts['heading']))
        <h2 class="text-2xl font-bold text-blue-800 mb-6">
            {{ $opts['heading'] }}
        </h2>
    @endif

    <div class="space-y-12 !ml-0">
        @foreach ($items as $item)
            @php
                $media = $item->featuredMedia->first();
                $showImage = !empty($opts['show_image']) && $media;
                $colClasses = $showImage ? 'md:col-span-2' : 'md:col-span-3';
                $title = $isProductTax ? $item->name : $item->title;
                $excerpt = Str::words(
                    strip_tags(BlockRenderer::render($item->description ?? $item->content)),
                    $opts['excerpt_words'] ?? 20,
                    '…',
                );
                $url = $isProductTax ? route('products.show', $item) : route('posts.show', $item);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                @if ($showImage)
                    <div class="md:col-span-1">
                        <a href="{{ $url }}" class="block overflow-hidden rounded-lg" style="aspect-ratio:4/3;">
                            <x-responsive-image :media="$media" :breakpoints="$breakpoints" sizes="(max-width:768px)100vw,33vw"
                                width="400" height="300" loading="lazy"
                                class="w-full h-full object-cover rounded-lg" alt="{{ $title }}" />
                        </a>
                    </div>
                @endif

                <div class="{{ $colClasses }} space-y-2">
                    <h3 class="text-xl font-semibold text-blue-800">{{ $title }}</h3>
                    <p class="text-gray-700">{{ $excerpt }}</p>

                    @if ($opts['button_type'] === 'read_more')
                        <a href="{{ $url }}" class="inline-block mt-2 text-blue-600 font-medium">
                            Read More
                        </a>
                    @elseif($opts['button_type'] === 'price' && $isProductTax && isset($item->price))
                        <span class="inline-block mt-2 text-lg font-semibold">
                            ${{ number_format($item->price, 2) }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
