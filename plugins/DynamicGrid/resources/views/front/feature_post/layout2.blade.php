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

    // Main featured post
    $main = $modelClass::find($opts['post_id'] ?? null);

    // All items in this taxonomy/category
    $items = $modelClass
        ::whereHas(
            $relation,
            fn($q) => $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']),
        )
        ->get();
@endphp

@if (!$main)
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No valid item found for ID {{ $opts['post_id'] ?? '(none)' }}.
    </div>
@else
    <div class="dynamic-grid feature-layout2 space-y-8">
        {{-- Block Heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-2xl font-bold text-blue-800">{{ $opts['heading'] }}</h2>
        @endif

        {{-- Main + First Two Items Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Left: Main post content --}}
            <div class="md:col-span-1 bg-white rounded shadow p-6 flex flex-col justify-center">
                <h3 class="text-xl font-semibold mb-4">{{ $main->name ?? $main->title }}</h3>
                <p class="text-gray-700">{{ strip_tags($main->description ?? $main->content) }}</p>
            </div>

            {{-- Right: First two items as cards --}}
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach ($items->take(2) as $item)
                    <div class="bg-white rounded shadow p-6 flex flex-col">
                        @if ($opts['show_image'] ?? false)
                            <x-responsive-image :media="optional($item->featuredMedia->first())" alt="{{ $item->name ?? $item->title }}"
                                class="w-full h-32 object-cover rounded mb-4" />
                        @endif

                        <h4 class="font-semibold text-lg mb-2">{{ $item->name ?? $item->title }}</h4>
                        <p class="flex-1 text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'] ?? 30, '…') }}
                        </p>

                        <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}"
                            class="mt-auto text-sm text-blue-600">
                            Read More
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Remaining Items Below --}}
        @if ($items->count() > 2)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($items->slice(2) as $item)
                    <div class="bg-white rounded shadow p-6 flex flex-col">
                        @if ($opts['show_image'] ?? false)
                            <x-responsive-image :media="optional($item->featuredMedia->first())" alt="{{ $item->name ?? $item->title }}"
                                class="w-full h-32 object-cover rounded mb-4" />
                        @endif

                        <h4 class="font-semibold text-lg mb-2">{{ $item->name ?? $item->title }}</h4>
                        <p class="flex-1 text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'] ?? 30, '…') }}
                        </p>

                        <a href="{{ $isProductTax ? route('products.show', $item) : route('posts.show', $item) }}"
                            class="mt-auto text-sm text-blue-600">
                            Read More
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
