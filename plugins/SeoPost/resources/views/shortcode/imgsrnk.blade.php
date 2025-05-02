{{-- plugins/SeoPost/resources/views/shortcode/imgsrnk.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

<div class="{{ $wrapperClass }}">
    @if ($products->isEmpty())
        <p>No items found.</p>
    @else
        <div class="grid grid-cols-{{ $settings['column'] }} gap-6">
            @foreach ($products as $product)
                <div class="border p-4 rounded">
                    <h3 class="font-semibold mb-2">{{ $product->name }}</h3>

                    @if ($settings['img'] === 'yes' && $product->featuredMedia->isNotEmpty())
                        @php
                            $media = $product->featuredMedia->first();
                        @endphp
                        <img src="{{ Storage::url($media->path) }}" alt="{{ $product->name }}"
                            class="w-full h-auto mb-2 rounded" />
                    @endif

                    <p>
                        {{ Str::limit($product->description, $settings['excerpt-hide'] ?? 100) }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
