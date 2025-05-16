@php
    use Illuminate\Support\Str;
    use App\Models\Post;
    use App\Models\Product;
    use App\Helpers\BlockRenderer;

    // 1) Main featured item always comes from Post
    $main = Post::find($opts['post_id'] ?? null);

    // 2) Decide model & relation for the right‐hand items
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? Product::class : Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // 3) Fetch two additional items in the same taxonomy/category
    $others = $modelClass
        ::whereHas($relation, function ($q) use ($opts) {
            $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
        })
        ->limit(2)
        ->get();
@endphp

@if (!$main)
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No valid post found for ID {{ $opts['post_id'] ?? '(none)' }}.
    </div>
@else
    <div class="dynamic-grid feature-layout1 space-y-6">
        {{-- Heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-2xl font-bold">{{ $opts['heading'] }}</h2>
        @endif

        {{-- 3-col grid: left featured (1col) + right two items (2cols) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Left: Featured Post --}}
            <div class="lg:col-span-1 flex flex-col items-start  p-6">
                <h2 class="text-blue-900 font-semibold text-2xl lg:text-3xl leading-tight mb-2 break-words">
                    {{ $main->title }}
                </h2>
                <div class="w-16 h-1 bg-red-300 mb-4"></div>

                {{--     @if ($opts['show_image'] && ($media = $main->featuredMedia->first()))
                    <x-responsive-image :media="$media" class="w-full h-full  mb-4" />
                @endif --}}

                <p class="flex-1 text-gray-700  mb-4">
                    {!! Str::words(strip_tags(BlockRenderer::render($main->content)), '…') !!}
                </p>

                @if ($opts['button_type'] === 'price')
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">
                        Get Price
                    </button>
                @elseif($opts['button_type'] === 'read_more')
                    {{--      <a href="{{ route('posts.show', $main) }}"
                        class="inline-block px-4 py-2 border border-blue-600 text-blue-600 rounded">
                        Read More
                    </a> --}}
                @endif
            </div>

            {{-- Right: Two items from Post or Product --}}
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($others as $item)
                    <div class=" w-full h-full  p-4 flex flex-col">
                        @if ($opts['show_image'] && ($media = $item->featuredMedia->first()))
                            <x-responsive-image :media="$media" class="w-full h-full   mb-3" />
                        @endif

                        <h3 class="font-semibold text-lg mb-2">
                            {{ $isProductTax ? $item->name : $item->title }}
                        </h3>

                        <p class="mt-2 text-gray-600 flex-1">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
