{{-- resources/views/plugins/DynamicGrid/templates/services_overview.blade.php --}}

@php
    use Illuminate\Support\Str;
    use App\Models\Post;
    use App\Models\Product;
    use App\Helpers\BlockRenderer;

    // Pick the right model & relation
    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? Product::class : Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Fetch main post (for both layouts)
    $main = $modelClass::find($opts['post_id'] ?? null);

    // Fetch all items in this category
    $items = $modelClass
        ::whereHas(
            $relation,
            fn($q) => $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']),
        )
        ->get();

    // Excerpt length
    $limit = intval($opts['excerpt_words'] ?? 20);

    // Responsive breakpoints
    $breakpoints = [150 => 'thumbnail', 300 => 'medium', 1024 => 'large'];
@endphp

@if (!$main)
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No valid item found for ID {{ $opts['post_id'] ?? '(none)' }}.
    </div>
@else
    {{-- Optional Heading --}}
    @if (!empty($opts['heading']))
        <h2 class="text-2xl font-bold text-blue-800 mb-6">
            {{ $opts['heading'] }}
        </h2>
    @endif

    @if ($opts['layout'] === 'layout2')
        {{-- FEATURE LAYOUT 2 --}}
        <div class="dynamic-grid feature-layout2 !ml-0 space-y-8">
            {{-- Main block --}}
            <div>
                <h3 class="text-xl font-semibold mb-2">{{ $main->name ?? $main->title }}</h3>
                <p class="text-gray-700">
                    {!! Str::words(strip_tags(BlockRenderer::render($main->description ?? $main->content)), $limit, '…') !!}
                </p>
            </div>

            {{-- Three equal columns --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($items->take(3) as $item)
                    @php
                        $media = $item->featuredMedia->first();
                        $showImage = !empty($opts['show_image']) && $media;
                        $title = $item->name ?? $item->title;
                        $url = $isProductTax ? route('products.show', $item) : route('posts.show', $item);
                        $excerpt2 = Str::words(
                            strip_tags(BlockRenderer::render($item->description ?? $item->content)),
                            $limit,
                            '…',
                        );
                    @endphp

                    <div class="bg-white flex flex-col">
                        @if ($showImage)
                            <div class="overflow-hidden rounded mb-4" style="aspect-ratio:4/3;">
                                <a href="{{ $url }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="$breakpoints"
                                        sizes="(max-width:768px)100vw,33vw" width="400" height="300" loading="lazy"
                                        class="w-full h-full object-cover rounded" alt="{{ $title }}" />
                                </a>
                            </div>
                        @endif

                        <h4 class="font-semibold text-lg mb-2 text-blue-800">{{ $title }}</h4>
                        <p class="flex-1 text-gray-600">{{ $excerpt2 }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- SERVICES-OVERVIEW (layout1) --}}
        <div class="dynamic-grid feature-layout1 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Left: Main content --}}
                <div class="md:col-span-1 bg-white rounded shadow p-6 flex flex-col justify-center">
                    <h3 class="text-xl font-semibold mb-2">{{ $main->name ?? $main->title }}</h3>
                    <p class="text-gray-700">
                        {!! Str::words(strip_tags(BlockRenderer::render($main->description ?? $main->content)), $limit, '…') !!}
                    </p>
                </div>

                {{-- Right: first two items --}}
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ($items->take(2) as $item)
                        @php
                            $media = $item->featuredMedia->first();
                            $showImage = !empty($opts['show_image']) && $media;
                            $title2 = $item->name ?? $item->title;
                            $url2 = $isProductTax ? route('products.show', $item) : route('posts.show', $item);
                            $excerpt3 = Str::words(
                                strip_tags(BlockRenderer::render($item->description ?? $item->content)),
                                $limit,
                                '…',
                            );
                        @endphp

                        <div class="bg-white rounded shadow p-6 flex flex-col">
                            @if ($showImage)
                                <div class="overflow-hidden rounded mb-4" style="aspect-ratio:4/3;">
                                    <a href="{{ $url2 }}" class="block w-full h-full">
                                        <x-responsive-image :media="$media" :breakpoints="$breakpoints"
                                            sizes="(max-width:768px)100vw,50vw" width="400" height="300"
                                            loading="lazy" class="w-full h-full object-cover rounded"
                                            alt="{{ $title2 }}" />
                                    </a>
                                </div>
                            @endif

                            <h4 class="font-semibold text-lg mb-2">{{ $title2 }}</h4>
                            <p class="flex-1 text-gray-600 mb-4">{{ $excerpt3 }}</p>
                            <a href="{{ $url2 }}" class="mt-auto text-sm text-blue-600">Read More</a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Remaining items --}}
            @if ($items->count() > 2)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($items->slice(2) as $item)
                        @php
                            $media = $item->featuredMedia->first();
                            $showImage = !empty($opts['show_image']) && $media;
                            $title3 = $item->name ?? $item->title;
                            $url3 = $isProductTax ? route('products.show', $item) : route('posts.show', $item);
                            $excerpt4 = Str::words(
                                strip_tags(BlockRenderer::render($item->description ?? $item->content)),
                                $limit,
                                '…',
                            );
                        @endphp

                        <div class="bg-white rounded shadow p-6 flex flex-col">
                            @if ($showImage)
                                <div class="overflow-hidden rounded mb-4" style="aspect-ratio:4/3;">
                                    <a href="{{ $url3 }}" class="block w-full h-full">
                                        <x-responsive-image :media="$media" :breakpoints="$breakpoints"
                                            sizes="(max-width:768px)100vw,33vw" width="400" height="300"
                                            loading="lazy" class="w-full h-full object-cover rounded"
                                            alt="{{ $title3 }}" />
                                    </a>
                                </div>
                            @endif

                            <h4 class="font-semibold text-lg mb-2">{{ $title3 }}</h4>
                            <p class="flex-1 text-gray-600 mb-4">{{ $excerpt4 }}</p>
                            <a href="{{ $url3 }}" class="mt-auto text-sm text-blue-600">Read More</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
