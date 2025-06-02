{{-- plugins/SliderPlugin/resources/views/front/slider.blade.php --}}

@once
    @push('head')
        {{-- Utility class for Ropa Sans; the main layout already preloads the font --}}
        <style>
            .font-ropa-sans {
                font-family: 'Ropa Sans', sans-serif;
            }
        </style>
    @endpush
@endonce

@php
    use Illuminate\Support\Facades\Storage;
    use Plugins\SliderPlugin\Models\Slider;
    use App\Models\Media;

    // Grab all active sliders (with their items)
    $sliders = Slider::where('is_active', true)->with('items')->get();

    // Default breakpoints for <x-responsive-image>
    $defaultBreakpoints = [
        150 => 'thumbnail',
        300 => 'medium',
        1024 => 'large',
    ];
@endphp

@if ($sliders->isEmpty())
    <div class="p-4 text-center text-red-600">No sliders found.</div>
@else
    @foreach ($sliders as $slider)
        @php
            $items = $slider->items;
            $count = $items->count();
        @endphp

        <section class="{{ $slider->layout === 'with-content' ? 'py-12' : '' }}">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8" x-data="{
                current: 0,
                slides: {{ $count }},
                showArrows: {{ $slider->show_arrows ? 'true' : 'false' }},
                showIndicators: {{ $slider->show_indicators ? 'true' : 'false' }},
                timer: null,
                init() {
                    if ({{ $slider->autoplay ? 'true' : 'false' }} && this.slides > 1) {
                        this.start();
                    }
                },
                start() {
                    this.pause();
                    this.timer = setInterval(() => this.next(), 5000);
                },
                pause() { clearInterval(this.timer) },
                next() { this.current = (this.current + 1) % this.slides },
                prev() { this.current = (this.current - 1 + this.slides) % this.slides }
            }" x-init="init()"
                @mouseenter="pause()" @mouseleave="start()">

                <div class="flex flex-col lg:flex-row overflow-hidden p-12 bg-[#f6f6f6]">

                    {{-- ◀ Image Carousel ▶ --}}
                    <div class="relative p-6 w-full lg:w-1/2 overflow-hidden" style="aspect-ratio:16/9;">
                        @foreach ($items as $i => $item)
                            @php
                                $media = $item->media_id ? Media::find($item->media_id) : null;
                            @endphp

                            {{-- Wrap BOTH content + picture in a single x-show wrapper --}}
                            <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                                class="absolute inset-0 flex flex-col justify-center items-center">
                                {{-- 1) If this slider has “with-content” layout, show heading + slogan first --}}
                                @if ($slider->layout === 'with-content')
                                    <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center z-10">
                                        <h2
                                            class="
                                                pl-7
                                                text-right
                                                font-light
                                                mb-[15px]
                                                block
                                                text-[#666666]
                                                text-[clamp(1.5rem,5vw,2.7rem)]
                                                uppercase
                                                font-ropa-sans
                                                leading-[1.2]
                                                tracking-normal
                                            ">
                                            {{ $slider->heading }}
                                        </h2>
                                        <p class="mt-4 text-lg text-gray-600 text-right">
                                            {{ $slider->slogan }}
                                        </p>
                                    </div>
                                @endif

                                {{-- 2) Now render the <picture> / <img> for this slide’s image --}}
                                @if ($media)
                                    @php
                                        // Build WebP URLs if you have them
                                        $largeWebp = $media->hasGeneratedConversion('large-webp')
                                            ? $media->getUrl('large-webp')
                                            : null;
                                        $mediumWebp = $media->hasGeneratedConversion('medium-webp')
                                            ? $media->getUrl('medium-webp')
                                            : null;
                                        $thumbWebp = $media->hasGeneratedConversion('thumbnail-webp')
                                            ? $media->getUrl('thumbnail-webp')
                                            : null;

                                        // Build JPEG/PNG fallback URLs
                                        $largeJpg = $media->hasGeneratedConversion('large')
                                            ? $media->getUrl('large')
                                            : $media->getUrl();
                                        $mediumJpg = $media->hasGeneratedConversion('medium')
                                            ? $media->getUrl('medium')
                                            : $media->getUrl();
                                        $thumbJpg = $media->hasGeneratedConversion('thumbnail')
                                            ? $media->getUrl('thumbnail')
                                            : $media->getUrl();
                                    @endphp

                                    <picture class="w-full h-full object-contain">
                                        {{-- a) AVIF source (optional if you generated <conversion>-avif) --}}
                                        @php
                                            $largeAvif = $media->hasGeneratedConversion('large-avif')
                                                ? $media->getUrl('large-avif')
                                                : null;
                                            $mediumAvif = $media->hasGeneratedConversion('medium-avif')
                                                ? $media->getUrl('medium-avif')
                                                : null;
                                            $thumbAvif = $media->hasGeneratedConversion('thumbnail-avif')
                                                ? $media->getUrl('thumbnail-avif')
                                                : null;
                                        @endphp
                                        @if ($largeAvif || $mediumAvif || $thumbAvif)
                                            <source type="image/avif"
                                                srcset="
                                                    @if ($largeAvif) {{ $largeAvif }} 1024w, @endif
                                                    @if ($mediumAvif) {{ $mediumAvif }} 400w, @endif
                                                    @if ($thumbAvif) {{ $thumbAvif }} 150w @endif
                                                "
                                                sizes="(min-width:1024px)50vw,100vw" />
                                        @endif

                                        {{-- b) WebP source (if you generated *-webp) --}}
                                        @if ($largeWebp || $mediumWebp || $thumbWebp)
                                            <source type="image/webp"
                                                srcset="
                                                    @if ($largeWebp) {{ $largeWebp }} 1024w, @endif
                                                    @if ($mediumWebp) {{ $mediumWebp }} 400w, @endif
                                                    @if ($thumbWebp) {{ $thumbWebp }} 150w @endif
                                                "
                                                sizes="(min-width:1024px)50vw,100vw" />
                                        @endif

                                        {{-- c) JPEG/PNG fallback --}}
                                        <img src="{{ $mediumJpg }}"
                                            srcset="
                                                {{ $largeJpg }} 1024w,
                                                {{ $mediumJpg }} 400w,
                                                {{ $thumbJpg }} 150w
                                            "
                                            sizes="(min-width:1024px)50vw,100vw"
                                            alt="{{ $media->getCustomProperty('alt') ?? '' }}"
                                            loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $i === 0 ? 'high' : 'low' }}" width="1024"
                                            height="576" class="w-full h-full object-contain" />
                                    </picture>
                                @else
                                    {{-- If no Media record, fall back to direct path --}}
                                    <img src="{{ Storage::url($item->image_path) }}" alt=""
                                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $i === 0 ? 'high' : 'low' }}" width="1024" height="576"
                                        sizes="(min-width:1024px)50vw,100vw" class="w-full h-full object-contain" />
                                @endif
                            </div>
                        @endforeach

                        {{-- ◀ Arrows ▶ --}}
                        <button x-show="showArrows" @click="prev()" aria-label="Previous slide"
                            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">‹</button>

                        <button x-show="showArrows" @click="next()" aria-label="Next slide"
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">›</button>

                        {{-- ◀ Indicators ▶ --}}
                        <div x-show="showIndicators" class="absolute bottom-0 left-1/2 -translate-x-1/2 flex space-x-2">
                            @for ($j = 0; $j < $count; $j++)
                                <button @click="current = {{ $j }}"
                                    :aria-current="current === {{ $j }} ? 'true' : 'false'"
                                    aria-label="Go to slide {{ $j + 1 }}"
                                    class="
                                        w-4 h-1 rounded-full
                                        opacity-50 scale-100
                                        transition-opacity transition-transform duration-200
                                    "
                                    :class="current === {{ $j }} ?
                                        'opacity-100 scale-125 bg-gray-800' :
                                        'opacity-50 scale-100 bg-gray-400/50'"
                                    style="will-change: opacity, transform;"></button>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach
@endif
