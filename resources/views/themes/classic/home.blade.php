@extends('layout')

@section('content')
         @php
    use App\Models\Slider;
    use App\Models\Category;
    $categories = Category::take(3)->get();
    $themeSettings = App\Models\ThemeSetting::where('theme', getActiveTheme())->first();
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

                          
                    @if ($themeSettings?->show_slider)
                        @include('partials.slider')
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
