@extends('layout')

@section('content')
                @php
                        use App\Models\Slider;
                        use App\Models\Category;
                        $categories = Category::take(3)->get();
                        $slides = Slider::with('images')
                            ->where('status', true)
                            ->where('slider_location', 'homepage')
                            ->orderBy('order')
                            ->get();
                    $featuredCategory = Category::where('slug', 'featured-products')->with([
                        'products' => function ($q) {
                            $q->where('status', 1);
                        }
                    ])->first();
                        $slidesJson = collect();

                        foreach ($slides as $slider) {
                            foreach ($slider->images as $image) {
                                $slidesJson->push([
                                    'imgSrc' => asset('storage/' . $image->image),
                                    'imgAlt' => $slider->title ?? '',
                                    'title' => $slider->title ?? '',
                                    'description' => $slider->subtitle ?? '',
                                ]);
                            }
                        }
                @endphp



                    @if($slides->count())
                    <!-- Hero Banner / Slider -->
                 <div x-data="sliderComponent()" x-init="init()" class="relative w-full overflow-hidden">
                <!-- Slides -->
                <div class="relative min-h-[100svh] w-full">
                    <template x-for="(slide, index) in slides" :key="index">
                        <div x-cloak x-show="currentSlideIndex === index + 1" class="absolute inset-0" x-transition.opacity.duration.1000ms>
                            <!-- Overlay content -->
                            <div class="lg:px-32 lg:py-14 absolute inset-0 z-10 flex flex-col items-center justify-end gap-2 bg-gradient-to-t from-black/80 to-transparent px-6 py-10 text-center">
                                <h3 class="w-full lg:w-[80%] text-balance text-2xl lg:text-4xl font-bold text-white" x-text="slide.title"></h3>
                                <p class="lg:w-1/2 w-full text-sm text-gray-200" x-text="slide.description"></p>
                            </div>

                            <!-- Fullscreen Image -->
                            <img 
                                class="absolute inset-0 w-full h-full object-cover object-center" 
                                :src="slide . imgSrc" 
                                :alt="slide . imgAlt" 
                            />
                        </div>
                    </template>
                </div>

               {{--  <!-- Pause/Play Button -->
                <button type="button"
                        class="absolute bottom-5 right-5 z-20 rounded-full text-white opacity-50 transition hover:opacity-80"
                        aria-label="pause carousel"
                        @click="isPaused = !isPaused; setAutoplayInterval(autoplayIntervalTime)"
                        :aria-pressed="isPaused">
                    <!-- Play Icon -->
                    <svg x-cloak x-show="isPaused" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" class="size-7">
                        <path d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm6.39-2.908a.75.75 0 0 1 .766.027l3.5 2.25a.75.75 0 0 1 0 1.262l-3.5 2.25A.75.75 0 0 1 8 12.25v-4.5a.75.75 0 0 1 .39-.658Z"/>
                    </svg>
                    <!-- Pause Icon -->
                    <svg x-cloak x-show="!isPaused" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" class="size-7">
                        <path d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm5-2.25a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-.5a.75.75 0 0 1-.75-.75v-4.5Zm4 0a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-.5a.75.75 0 0 1-.75-.75v-4.5Z"/>
                    </svg>
                </button>
             --}}
                <!-- Indicators -->
                <div class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 gap-3 px-2" role="group" aria-label="slides">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="currentSlideIndex = index + 1; setAutoplayInterval(autoplayIntervalTime)"
                                class="size-2 rounded-full transition"
                                :class="currentSlideIndex === index + 1 ? 'bg-white' : 'bg-white/50'"
                                :aria-label="'Slide ' + (index + 1)"></button>
                    </template>
                </div>
            </div>

                    @endif

                    <!-- Categories -->
                    <section class="bg-gray-50 py-12">
                        <div class="container mx-auto px-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                @foreach ($categories as $category)
                                    <a href="{{ route('category.show', $category->slug) }}" class="block overflow-hidden group">
                                        <div class="relative h-auto overflow-hidden rounded shadow-md">
                                            <img src="{{ asset('storage/' . $category->featured_image) }}" alt="{{ $category->name }}"
                                                class="object-cover w-full h-full transform group-hover:scale-105 transition duration-300">
                                            <div class="absolute bottom-0 w-full bg-black/80 text-white py-2 text-center">
                                                <h3 class="text-xl font-extrabold tracking-wide uppercase">{{ $category->name }}</h3>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>





                    <!-- Features Prodicts -->


                    @if($featuredCategory && $featuredCategory->products->count())
                        <section class="py-12">
                            <div class="max-w-7xl mx-auto px-4">
                                <h2 class="text-center text-3xl font-bold mb-10">FEATURED PRODUCTS</h2>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    @foreach($featuredCategory->products as $product)
                                        <div class="text-center group">
                                            <a href="{{ route('product.show', $product->slug) }}">
                                                <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}"
                                                    class="w-full h-80 object-cover border border-gray-200 rounded shadow-sm transition duration-300 group-hover:scale-105">
                                            </a>

                                            <h3 class="mt-3 text-sm font-bold uppercase">{{ $product->name }}</h3>

                                            {{-- Placeholder for future ratings --}}
                                            <div class="text-xs text-yellow-400 mt-1">☆☆☆☆☆</div>

                                            <a href="{{ route('product.show', $product->slug) }}"
                                                class="mt-2 inline-block bg-black text-white px-4 py-2 text-sm font-semibold rounded hover:bg-gray-800 transition">
                                                READ MORE
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif





@endsection

@push('scripts')
<script>
function sliderComponent() {
    return {
        autoplayIntervalTime: 6000,
        slides: {!! $slidesJson->toJson() !!},
        currentSlideIndex: 1,
        isPaused: false,
        autoplayInterval: null,
        previous() {
            this.currentSlideIndex = this.currentSlideIndex > 1
                ? this.currentSlideIndex - 1
                : this.slides.length;
        },
        next() {
            this.currentSlideIndex = this.currentSlideIndex < this.slides.length
                ? this.currentSlideIndex + 1
                : 1;
        },
        autoplay() {
            this.autoplayInterval = setInterval(() => {
                if (!this.isPaused) this.next();
            }, this.autoplayIntervalTime);
        },
        setAutoplayInterval(newIntervalTime) {
            clearInterval(this.autoplayInterval);
            this.autoplayIntervalTime = newIntervalTime;
            this.autoplay();
        },
        init() {
            this.autoplay();
        }
    };
}
</script>
@endpush
