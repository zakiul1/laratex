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

    // Fetch all active sliders (with their items)
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

        {{-- PURE HERO LAYOUT --}}
        @if ($slider->layout === 'pure' && $count)
            @php
                // take the first slide for the pure hero
                $item = $items->first();
                $media = $item->media_id ? Media::find($item->media_id) : null;
                $content = $item->content ?? [];
                $buttons = $content['buttons'] ?? [];
            @endphp

            <section class="relative w-full hero-75vh md:hero-85vh lg:hero-90vh overflow-hidden mb-20">
                {{-- background image --}}
                <div class="absolute inset-0">
                    @if ($media)
                        <x-responsive-image :media="$media" :breakpoints="[
                            150 => 'thumbnail',
                            300 => 'medium',
                            480 => 'mobile',
                            768 => 'tablet',
                            1024 => 'large',
                        ]" class="w-full h-full object-cover"
                            alt="{{ $media->getCustomProperty('alt') ?? '' }}" loading="eager" fetchpriority="high" />
                    @else
                        <img src="{{ Storage::url($item->image_path) }}" class="w-full h-full object-cover" alt=""
                            loading="eager" fetchpriority="high" />
                    @endif

                    {{-- dark overlay --}}
                    <div class="absolute inset-0 bg-black/50"></div>
                </div>

                {{-- content --}}
                <div
                    class="relative z-10 flex flex-col items-center justify-center text-center text-white h-full px-4 sm:px-6 lg:px-8">
                    <h1 class="max-w-3xl text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                        {{ $content['title'] ?? '' }}
                    </h1>
                    @if (!empty($content['subtitle']))
                        <p class="mt-4 max-w-2xl text-lg sm:text-xl md:text-2xl">
                            {{ $content['subtitle'] }}
                        </p>
                    @endif

                    @if (count($buttons))
                        <div class="mt-8 flex flex-wrap justify-center gap-4">
                            @foreach ($buttons as $btn)
                                @if ($loop->first)
                                    {{-- Primary: blue → red on hover --}}
                                    <a href="{{ $btn['url'] ?? '#' }}"
                                        class="
            cursor-pointer
            px-6 py-3
            bg-blue-600 text-white
            font-semibold rounded shadow
            transition-colors duration-200
            hover:bg-red-500
          ">
                                        {{ $btn['text'] ?? 'Request a Quote' }}
                                    </a>
                                @else
                                    {{-- Secondary: outline → solid blue on hover --}}
                                    <a href="{{ $btn['url'] ?? '#' }}"
                                        class="
            cursor-pointer
            px-6 py-3
            border border-blue-600
            text-blue-600
            font-semibold rounded
            transition-colors duration-200
            hover:bg-blue-600 hover:text-white
          ">
                                        {{ $btn['text'] ?? 'Learn More' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif



                </div>
            </section>

            {{-- CAROUSEL LAYOUT (unchanged) --}}
        @elseif ($slider->layout === 'carousel')
            <section class="py-6">
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
                    {{-- IMAGE + ARROWS --}}
                    <div class="relative overflow-hidden rounded-lg group" style="aspect-ratio:16/9;">
                        @foreach ($items as $i => $item)
                            @php $media = $item->media_id ? Media::find($item->media_id) : null; @endphp
                            <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                                class="absolute inset-0">
                                @if ($media)
                                    <x-responsive-image :media="$media" :breakpoints="[
                                        150 => 'thumbnail',
                                        300 => 'medium',
                                        480 => 'mobile',
                                        768 => 'tablet',
                                        1024 => 'large',
                                    ]"
                                        class="w-full h-full object-contain"
                                        alt="{{ $media->getCustomProperty('alt') ?? '' }}"
                                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
                                        sizes="(max-width:640px)100vw,(max-width:1024px)80vw,50vw" width="1024"
                                        height="576" />
                                @else
                                    <img src="{{ Storage::url($item->image_path) }}"
                                        class="w-full h-full object-contain" alt=""
                                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
                                        sizes="(max-width:640px)100vw,(max-width:1024px)80vw,50vw" width="1024"
                                        height="576" />
                                @endif
                            </div>
                        @endforeach

                        {{-- ← Left Arrow --}}
                        <button x-show="showArrows" @click="prev()" aria-label="Previous slide"
                            class="absolute left-2 top-1/2 transform -translate-y-1/2 z-10 bg-white/80 p-2 rounded-full shadow hover:bg-white
                                       opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            ‹
                        </button>

                        {{-- Right Arrow → --}}
                        <button x-show="showArrows" @click="next()" aria-label="Next slide"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 z-10 bg-white/80 p-2 rounded-full shadow hover:bg-white
                                       opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            ›
                        </button>
                    </div>

                    {{-- INDICATORS --}}
                    <div x-show="showIndicators" class="mt-4 flex justify-center space-x-2">
                        @for ($j = 0; $j < $count; $j++)
                            <button @click="current = {{ $j }}"
                                aria-label="Go to slide {{ $j + 1 }}"
                                class="w-4 h-1 rounded-full transition-opacity transition-transform duration-200"
                                :class="current === {{ $j }} ?
                                    'opacity-100 scale-125 bg-gray-800' :
                                    'opacity-50 scale-100 bg-gray-400/50'">
                            </button>
                        @endfor
                    </div>
                </div>
            </section>

            {{-- WITH-CONTENT LAYOUT (unchanged) --}}
        @else
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

                    <div class="flex flex-col lg:flex-row overflow-hidden p-14 bg-[#f6f6f6]">
                        {{-- IMAGE & SLIDE --}}
                        <div class="relative p-6 w-full lg:w-1/2 overflow-hidden group" style="aspect-ratio:16/9;">
                            @foreach ($items as $i => $item)
                                @php $media = $item->media_id ? Media::find($item->media_id) : null; @endphp
                                <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                                    class="absolute inset-0">
                                    @if ($media)
                                        <x-responsive-image :media="$media" :breakpoints="[
                                            150 => 'thumbnail',
                                            300 => 'medium',
                                            480 => 'mobile',
                                            768 => 'tablet',
                                            1024 => 'large',
                                        ]"
                                            class="w-full h-full object-contain"
                                            alt="{{ $media->getCustomProperty('alt') ?? '' }}"
                                            loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
                                            sizes="(max-width:640px)100vw,(max-width:1024px)60vw,50vw" width="1024"
                                            height="576" />
                                    @else
                                        <img src="{{ Storage::url($item->image_path) }}"
                                            class="w-full h-full object-contain" alt=""
                                            loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
                                            sizes="(max-width:640px)100vw,(max-width:1024px)60vw,50vw" width="1024"
                                            height="576" />
                                    @endif
                                </div>
                            @endforeach

                            {{-- ← Left Arrow --}}
                            <button x-show="showArrows" @click="prev()" aria-label="Previous slide"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white
                                           opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                ‹
                            </button>
                            {{-- Right Arrow → --}}
                            <button x-show="showArrows" @click="next()" aria-label="Next slide"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white
                                           opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                ›
                            </button>

                            {{-- ◀ Indicators (inside image) ▶ --}}
                            <div x-show="showIndicators"
                                class="absolute bottom-0 left-1/2 -translate-x-1/2 flex space-x-2 p-4">
                                @for ($j = 0; $j < $count; $j++)
                                    <button @click="current = {{ $j }}"
                                        aria-label="Go to slide {{ $j + 1 }}"
                                        class="w-4 h-1 rounded-full transition-opacity transition-transform duration-200"
                                        :class="current === {{ $j }} ?
                                            'opacity-100 scale-125 bg-gray-800' :
                                            'opacity-50 scale-100 bg-gray-400/50'">
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- HEADING & SLOGAN (with-content) --}}
                        @if ($slider->layout === 'with-content')
                            <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center">
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
                    </div>
                </div>
            </section>
        @endif
    @endforeach
@endif
