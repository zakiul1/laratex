{{-- plugins/SliderPlugin/resources/views/front/slider.blade.php --}}

@php
    use Plugins\SliderPlugin\Models\Slider;
    $sliders = Slider::where('is_active', true)->with('items')->get();
@endphp

@if ($sliders->isEmpty())
    <div class="p-4 text-center text-red-600">No sliders found.</div>
@else
    {{-- load Alpine once --}}
    @once
        {{--  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    @endonce

    {{-- Alpine factory --}}
    @once
        <script>
            window.sliderComponent = function(cfg) {
                return {
                    slides: cfg.slides,
                    layout: cfg.layout, // "pure" or "with-content"
                    autoplay: cfg.autoplay,
                    showArrows: cfg.showArrows,
                    showIndicators: cfg.showIndicators,
                    interval: cfg.interval || 4000,
                    current: 0,
                    timer: null,

                    init() {
                        if (this.autoplay && this.slides.length > 1) {
                            this.start();
                        }
                    },
                    start() {
                        this.pause();
                        this.timer = setInterval(() => this.next(), this.interval);
                    },
                    pause() {
                        clearInterval(this.timer);
                    },
                    next() {
                        this.current = (this.current + 1) % this.slides.length;
                    },
                    prev() {
                        this.current = this.current > 0 ? this.current - 1 : this.slides.length - 1;
                    },
                    goTo(i) {
                        this.current = i;
                    }
                };
            }
        </script>
    @endonce

    @foreach ($sliders as $slider)
        @php
            $slides = $slider->items
                ->map(
                    fn($it) => [
                        'imgSrc' => asset("storage/{$it->image_path}"),
                        'imgAlt' => $it->content['title'] ?? '',
                        'title' => $it->content['title'] ?? '',
                        'description' => $it->content['subtitle'] ?? '',
                        'buttons' => $it->content['buttons'] ?? [],
                    ],
                )
                ->values()
                ->toArray();

            $jsonSlides = json_encode($slides, JSON_UNESCAPED_SLASHES);
            $layout = json_encode($slider->layout);
            $autoplay = $slider->autoplay ? 'true' : 'false';
            $showArrows = $slider->show_arrows ? 'true' : 'false';
            $showIndicators = $slider->show_indicators ? 'true' : 'false';
        @endphp

        <div x-data='sliderComponent({
            slides: {!! $jsonSlides !!},
            layout: {!! $layout !!},
            autoplay: {{ $autoplay }},
            showArrows: {{ $showArrows }},
            showIndicators: {{ $showIndicators }},
            interval: 3000
        })'
            x-init="init()" @mouseenter="pause()" @mouseleave="start()"
            class="relative w-full h-48 sm:h-64 md:h-80 lg:h-screen overflow-hidden">

            {{-- Slides --}}
            <template x-for="(s, i) in slides" :key="i">
                <div x-show="current === i" x-transition.opacity.duration.700ms
                    class="absolute inset-0 flex items-center justify-center bg-black">
                    <div
                        :class="layout === 'with-content'
                            ?
                            'flex flex-col sm:flex-row w-full h-full' :
                            'w-full h-full flex items-center justify-center'">
                        {{-- Image --}}
                        <img :src="s.imgSrc" :alt="s.imgAlt"
                            :class="layout === 'with-content'
                                ?
                                'sm:w-1/2 h-full object-cover' :
                                'w-full h-full object-cover'" />

                        {{-- Content --}}
                        <div x-show="layout==='with-content'"
                            class="sm:w-1/2 bg-black/50 p-4 sm:p-6 md:p-8 flex flex-col justify-center text-white">
                            <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold" x-text="s.title"></h3>
                            <p class="mt-2 text-xs sm:text-sm md:text-base" x-text="s.description"></p>
                            <div class="mt-4 space-x-2">
                                <template x-for="(btn, bi) in s.buttons" :key="bi">
                                    <a :href="btn.url"
                                        class="px-2 py-1 sm:px-3 sm:py-2 text-xs sm:text-sm md:text-base rounded transition bg-blue-600 hover:bg-blue-700"
                                        x-text="btn.text"></a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Prev/Next Arrows --}}
            <button x-show="showArrows" @click="prev()"
                class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-white/70 p-1 sm:p-2 rounded-full transition opacity-75 hover:opacity-100"
                x-transition>
                ‹
            </button>
            <button x-show="showArrows" @click="next()"
                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-white/70 p-1 sm:p-2 rounded-full transition opacity-75 hover:opacity-100"
                x-transition>
                ›
            </button>

            {{-- Indicators --}}
            <div x-show="showIndicators"
                class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 flex space-x-1 sm:space-x-2">
                <template x-for="(_, idx) in slides" :key="idx">
                    <button @click="goTo(idx)" class="w-4 sm:w-6 h-1 sm:h-1 rounded-full transition"
                        :class="current === idx ? 'bg-white' : 'bg-white/50'"></button>
                </template>
            </div>

        </div>
    @endforeach

@endif
