{{-- resources/views/plugins/DynamicGrid/templates/single_layout2.blade.php --}}

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
        $query->take((int) $opts['product_amount']);
    }

    $items = $query->get();

    // Responsive breakpoints for <x-responsive-image>
    $breakpoints = [
        150 => 'thumbnail',
        300 => 'medium',
        480 => 'mobile',
        768 => 'tablet',
        1024 => 'large',
    ];
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800">
        No items found for “{{ $opts['taxonomy'] }}” & category {{ $opts['category_id'] }}.
    </div>
@else
    <div class="space-y-8 !ml-0">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-3xl font-bold text-center mb-2">
                {{ $opts['heading'] }}
            </h2>
            @if (!empty($opts['subheading']))
                <p class="text-gray-600 text-center max-w-2xl mx-auto mb-6">
                    {{ $opts['subheading'] }}
                </p>
            @endif
        @endif

        {{-- Single column on mobile (≤768px); two columns at md (≥768px) --}}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            @foreach ($items as $item)
                @php
                    $media = $item->featuredMedia->first();
                    $title = $isProductTax ? $item->name : $item->title;
                    $excerpt = Str::words(
                        strip_tags($item->description ?? $item->content),
                        $opts['excerpt_words'] ?? 30,
                        '…',
                    );
                    $url = $isProductTax ? route('products.show', $item->slug) : route('posts.show', $item->slug);

                    // Force image display for single_post + layout2
                    $forceShowImage = $opts['type'] === 'single_post' && $opts['layout'] === 'layout2';
                    $shouldShowImage = !empty($opts['show_image']) || $forceShowImage;

                    // Only show excerpt if excerpt_words > 0
                    $showExcerpt = isset($opts['excerpt_words']) && (int) $opts['excerpt_words'] > 0;
                @endphp

                <div class="flex flex-col md:flex-row md:space-x-6">
                    {{-- Image column --}}
                    <div class="w-full md:w-1/3 flex-shrink-0 mb-4 md:mb-0">
                        @if ($shouldShowImage && $media)
                            <div class="overflow-hidden" style="aspect-ratio:1/1;">
                                <a href="{{ $url }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="$breakpoints"
                                        sizes="(max-width: 768px) 100vw, 50vw" width="400" height="400"
                                        loading="lazy" class="w-full h-full object-contain" alt="{{ $title }}" />
                                </a>
                            </div>
                        @else
                            <div class="overflow-hidden bg-gray-100 flex items-center justify-center text-gray-400"
                                style="aspect-ratio:1/1;">
                                —
                            </div>
                        @endif
                    </div>

                    {{-- Text column --}}
                    <div class="w-full md:w-2/3 space-y-2">
                        <h3 class="text-2xl text-[#0e4f7f]">
                            <a href="{{ $url }}" class="hover:text-blue-600 transition">
                                {{ $title }}
                            </a>
                        </h3>

                        @if ($showExcerpt)
                            <p class="text-gray-600 leading-loose text-justify">
                                {{ $excerpt }}
                            </p>
                        @endif

                        {{-- Read More link with descriptive aria-label --}}
                        <a href="{{ $url }}"
                            class="inline-flex items-center text-blue-600 font-medium hover:underline"
                            aria-label="Read more about {{ $title }}">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        @if (($opts['button_type'] ?? '') === 'price')
                            <button type="button"
                                class="get-price-btn mt-4 px-4 py-2 text-blue-600 font-medium border-b-2 border-blue-600 hover:text-blue-800"
                                data-id="{{ $item->id }}" data-title="{{ e($title) }}"
                                data-image="{{ $media?->getUrl('thumbnail') }}" data-url="{{ $url }}">
                                Get Price
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
