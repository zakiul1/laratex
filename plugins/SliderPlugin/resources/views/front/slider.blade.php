{{-- plugins/SliderPlugin/resources/views/front/slider.blade.php --}}

@once
    {{-- 1) Load Ropa Sans from Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Ropa+Sans&display=swap" rel="stylesheet" />
    {{-- 2) Define our helper class --}}
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
        @php $count = $slider->items->count(); @endphp

        @if ($slider->layout === 'with-content')
            <section class="py-12">
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
                        <div class="relative w-full lg:w-1/2 h-64 sm:h-80 lg:h-[400px]">
                            @foreach ($slider->items as $i => $item)
                                @php $media = $item->media_id ? Media::find($item->media_id) : null; @endphp
                                <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                                    class="absolute inset-0">
                                    @if ($media)
                                        <x-responsive-image :media="$media" :breakpoints="[150 => 'thumbnail', 300 => 'medium', 1024 => 'large']"
                                            class="w-full h-full object-cover" alt="" />
                                    @else
                                        <img src="{{ Storage::url($item->image_path) }}"
                                            class="w-full h-full object-cover" loading="lazy" alt="" />
                                    @endif
                                </div>
                            @endforeach

                            {{-- ◀ Arrows ▶ --}}
                            <button x-show="showArrows" @click="prev()"
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">‹</button>
                            <button x-show="showArrows" @click="next()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white">›</button>

                            {{-- ◀ Indicators ▶ --}}
                            <div x-show="showIndicators"
                                class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                                @for ($i = 0; $i < $count; $i++)
                                    <button @click="current = {{ $i }}"
                                        class="w-4 h-1 rounded-full transition"
                                        :class="current === {{ $i }} ? 'bg-gray-800' : 'bg-gray-400/50'"></button>
                                @endfor
                            </div>
                        </div>

                        {{-- ◀ Heading & Slogan ▶ --}}
                        <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center">
                            {{-- wrapper with pl-7 (28px), right-align & font-light (300) --}}
                            <div class="pl-7 text-right font-light mb-[15px]">
                                <h2
                                    class="
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
                            </div>

                            <p class="mt-4 text-lg text-gray-600  text-right">
                                {{ $slider->slogan }}
                            </p>
                        </div>


                    </div>
                </div>
            </section>
        @else
            {{-- ——— Pure layout (no content) ——— --}}
            <div class="relative w-full h-48 sm:h-64 md:h-80 lg:h-screen overflow-hidden" x-data="{
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
            }"
                x-init="init()" @mouseenter="pause()" @mouseleave="start()">
                @foreach ($slider->items as $i => $item)
                    @php $media = $item->media_id ? Media::find($item->media_id) : null; @endphp
                    <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms
                        class="absolute inset-0">
                        @if ($media)
                            <x-responsive-image :media="$media" :breakpoints="[150 => 'thumbnail', 300 => 'medium', 1024 => 'large']" class="w-full h-full object-cover"
                                alt="" />
                        @else
                            <img src="{{ Storage::url($item->image_path) }}" class="w-full h-full object-cover"
                                loading="lazy" alt="" />
                        @endif
                    </div>
                @endforeach

                {{-- ◀ Arrows ▶ --}}
                <button x-show="showArrows" @click="prev()"
                    class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-white/70 p-1 sm:p-2 rounded-full opacity-75 hover:opacity-100">‹</button>
                <button x-show="showArrows" @click="next()"
                    class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-white/70 p-1 sm:p-2 rounded-full opacity-75 hover:opacity-100">›</button>

                {{-- ◀ Indicators ▶ --}}
                <div x-show="showIndicators"
                    class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 flex space-x-1 sm:space-x-2">
                    @for ($i = 0; $i < $count; $i++)
                        <button @click="current = {{ $i }}"
                            class="w-4 sm:w-6 h-1 sm:h-1 rounded-full transition"
                            :class="current === {{ $i }} ? 'bg-white' : 'bg-white/50'"></button>
                    @endfor
                </div>
            </div>
        @endif
    @endforeach
@endif
