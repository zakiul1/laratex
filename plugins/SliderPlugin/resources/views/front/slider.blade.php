{{-- plugins/SliderPlugin/resources/views/front/slider.blade.php --}}

@once
    {{-- 1) Preconnect & load Ropa Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ropa+Sans&display=swap" rel="stylesheet">
    <style>
        .font-ropa-sans {
            font-family: 'Ropa Sans', sans-serif;
        }
    </style>
@endonce

@php
    use Illuminate\Support\Facades\Storage;
    use Plugins\SliderPlugin\Models\Slider;
    use App\Models\Media;

    $sliders = Slider::where('is_active', true)->with('items')->get();
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
                init() { if ({{ $slider->autoplay ? 'true' : 'false' }} && this.slides > 1) this.start() },
                start() {
                    this.pause();
                    this.timer = setInterval(() => this.next(), 5000)
                },
                pause() { clearInterval(this.timer) },
                next() { this.current = (this.current + 1) % this.slides },
                prev() { this.current = (this.current - 1 + this.slides) % this.slides }
            }" x-init="init()"
                @mouseenter="pause()" @mouseleave="start()">

                <div class="flex flex-col lg:flex-row overflow-hidden bg-gray-100">

                    {{-- ◀ Image Carousel ▶ --}}
                    <div class="relative w-full lg:w-1/2 overflow-hidden" style="aspect-ratio:16/9;">
                        @foreach ($items as $i => $item)
                            @php $media = $item->media_id ? Media::find($item->media_id) : null; @endphp
                            <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                                class="absolute inset-0">
                                @if ($media)
                                    <picture>
                                        {{-- AVIF first --}}
                                        <source type="image/avif" srcset="{{ $media->getUrl('large-avif') }}"
                                            sizes="(min-width:1024px)50vw,100vw">
                                        {{-- then WebP --}}
                                        <source type="image/webp" srcset="{{ $media->getUrl('large-webp') }}"
                                            sizes="(min-width:1024px)50vw,100vw">
                                        {{-- fallback JPEG/PNG --}}
                                        <img src="{{ $media->getUrl('large') }}" alt=""
                                            loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $i === 0 ? 'high' : 'low' }}" width="1024"
                                            height="576" sizes="(min-width:1024px)50vw,100vw"
                                            class="w-full h-full object-cover">
                                    </picture>
                                @else
                                    <img src="{{ Storage::url($item->image_path) }}" alt=""
                                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $i === 0 ? 'high' : 'low' }}" width="1024" height="576"
                                        sizes="(min-width:1024px)50vw,100vw" class="w-full h-full object-cover" />
                                @endif
                            </div>
                        @endforeach

                        {{-- ◀ Arrows ▶ --}}
                        <button x-show="showArrows" @click="prev()"
                            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">
                            ‹
                        </button>
                        <button x-show="showArrows" @click="next()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">
                            ›
                        </button>

                        {{-- ◀ Indicators ▶ --}}
                        <div x-show="showIndicators" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                            @for ($j = 0; $j < $count; $j++)
                                <button @click="current = {{ $j }}" class="w-4 h-1 rounded-full transition"
                                    :class="current === {{ $j }} ? 'bg-gray-800' : 'bg-gray-400/50'">
                                </button>
                            @endfor
                        </div>
                    </div>

                    {{-- ◀ Heading & Slogan ▶ --}}
                    @if ($slider->layout === 'with-content')
                        <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center">
                            <div class="pl-7 text-right font-light mb-[15px]">
                                <h2
                                    class="block text-[#666666]
                                         text-[clamp(1.5rem,5vw,2.7rem)]
                                         uppercase font-ropa-sans
                                         leading-[1.2] tracking-normal">
                                    {{ $slider->heading }}
                                </h2>
                            </div>
                            <p class="mt-4 text-lg text-gray-600 text-right">
                                {{ $slider->slogan }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </section>
    @endforeach
@endif
