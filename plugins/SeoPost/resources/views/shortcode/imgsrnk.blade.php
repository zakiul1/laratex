{{-- plugins/SeoPost/resources/views/shortcode/imgsrnk.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="{{ \$wrapperClass }} grid grid-cols-{{ \$settings['mcol'] }} sm:grid-cols-{{ \$settings['tcol'] }} lg:grid-cols-{{ \$settings['column'] }} gap-6">
    @foreach(\$posts as \$post)
        <div class="seopost-item imgsrnk-card {{ \$settings['c-class'] }}">
            {{-- Featured Image --}}
            @if(\$settings['img'] === 'yes' && isset(\$post->thumbnail_url))
                <img src="{{ \$post->thumbnail_url }}" alt="{{ \$post->title }}" class="w-full mb-4 rounded" />
            @endif

            {{-- Title / Stamp --}}
            <h2 class="text-2xl font-bold mb-2">{{ \$post->title }}</h2>

            {{-- Icon if enabled --}}
            @if(\$settings['icon'] === 'yes')
                <div class="mb-2">
                    {{-- Example icon; replace with your SVG or FontAwesome as needed --}}
                    <i class="fas fa-star"></i>
                </div>
            @endif

            {{-- Excerpt (limited if excerpt-hide set) --}}
            @if(\$settings['excerpt-hide'])
                <p class="text-gray-600">
                    {{ Str::limit(\$post->excerpt, (int) \$settings['excerpt-hide']) }}
                </p>
            @endif

            {{-- Get Price button --}}
            @if(\$settings['get-price'] === 'yes')
                <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                    Get Price
                </button>
            @endif
        </div>
    @endforeach
</div>
