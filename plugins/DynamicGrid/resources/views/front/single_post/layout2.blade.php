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
        $query->take(intval($opts['product_amount']));
    }

    $items = $query->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” & category {{ $opts['category_id'] }}.
    </div>
@else
    <div class="space-y-8 !ml-0">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-3xl font-bold text-center">{{ $opts['heading'] }}</h2>
            @if (!empty($opts['subheading']))
                <p class="text-gray-600 text-center max-w-2xl mx-auto">
                    {{ $opts['subheading'] }}
                </p>
            @endif
        @endif

        {{-- Single column on mobile, two columns on md+ --}}
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
                @endphp

                <div class="flex flex-col md:flex-row md:space-x-6">
                    {{-- Image --}}
                    <div class="w-full md:w-1/3 flex-shrink-0 mb-4 md:mb-0">
                        @if (!empty($opts['show_image']) && $media)
                            <a href="{{ $url }}" class="block overflow-hidden hover:shadow-md transition">
                                <x-responsive-image :media="$media" alt="{{ $title }}"
                                    class="w-full h-auto object-cover" />
                            </a>
                        @else
                            <div
                                class="w-full h-32 bg-gray-100 flex items-center justify-center rounded-lg text-gray-400">
                                —
                            </div>
                        @endif
                    </div>

                    {{-- Text --}}
                    <div class="w-full md:w-2/3 space-y-2">
                        <h3 class="text-xl font-semibold text-blue-800">
                            <a href="{{ $url }}" class="hover:text-blue-600 transition">
                                {{ $title }}
                            </a>
                        </h3>

                        @if (!empty($opts['show_description']))
                            <p class="text-gray-600 leading-relaxed">{{ $excerpt }}</p>
                        @endif

                        <a href="{{ $url }}"
                            class="inline-flex items-center text-blue-600 font-medium hover:underline">
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
                                data-image="{{ $media ? $media->getUrl() : '' }}" data-url="{{ $url }}">
                                Get Price
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
