{{-- resources/views/themes/siatexbd/templates/siatex-global.blade.php --}}
@extends('themes.siatexbd.layout')

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    @if (!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@php
    use App\Models\Media;
    use App\Models\TermTaxonomy;

    // Find the media_category "global"
    $globalCatId = TermTaxonomy::where('taxonomy', 'media_category')
        ->whereHas('term', fn($q) => $q->where('name', 'global'))
        ->value('term_taxonomy_id');

    // Build slides from Media attached to that category
    $mediaItems = $globalCatId
        ? Media::whereHas('categories', fn($q) => $q->where('term_taxonomies.term_taxonomy_id', $globalCatId))
            ->latest()
            ->take(12)
            ->get()
        : collect();

    // Map to {src, alt}
    $slides = $mediaItems
        ->map(function ($m) {
            $url =
                method_exists($m, 'hasGeneratedConversion') && $m->hasGeneratedConversion('large')
                    ? $m->getUrl('large')
                    : (method_exists($m, 'getUrl')
                        ? $m->getUrl()
                        : null);

            return [
                'src' => $url,
                'alt' => $m->filename ?? 'Slide',
            ];
        })
        ->values()
        ->all();

    // Fallback if no images found
    if (empty($slides)) {
        $slides = [['src' => null, 'alt' => 'Image not found']];
    }
@endphp

@push('styles')
    {{-- Keep Poppins to mirror the provided design --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
    {{-- ==== Page content (title + $pageOutput) ==== --}}
    {{--     <div class="container mx-auto px-4 py-8">
        <h1
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight sm:tracking-wide font-oswald text-center mb-4">
            {{ $page->title }}
        </h1>

        <div class="prose prose-sm sm:prose lg:prose-lg max-w-none text-justify">
            {!! apply_filters('the_content', $pageOutput) !!}
        </div>
    </div> --}}

    {{-- ==== SECTION 1: Siatex Global (Carousel) ==== --}}

    <section class="bg-[#f4f4f9] font-[Poppins]">
        <div class="max-w-7xl mx-auto px-4 py-10 container">
            <header class="text-center mb-12">
                <h2 class="font-oswald text-3xl md:text-5xl font-bold text-[#2c3e50]">Siatex Global</h2>
                <p class="text-base md:text-lg text-[#333] mt-2">Your partner for custom athleticwear and sportswear
                    manufacturing.</p>
            </header>

            <main class="space-y-8">
                {{-- Row One: 7 / 5 split --}}
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    {{-- Carousel (7 columns) --}}
                    <div class="lg:col-span-7 bg-[#e9ecef] rounded-lg shadow hover:shadow-lg transition"
                        x-data="siatexCarousel({ slides: @js($slides), auto: true, interval: 5000 })" x-init="init()">
                        <div class="relative p-4 overflow-hidden">
                            <div class="overflow-hidden">
                                <div class="flex transition-transform duration-500"
                                    :style="`transform: translateX(-${current * slideWidth}px)`" x-ref="track">
                                    <template x-for="(s, i) in slides" :key="i">
                                        <div class="w-full flex-shrink-0">
                                            <template x-if="s.src">
                                                <img :src="s.src" :alt="s.alt"
                                                    class="w-full aspect-square object-cover rounded-lg" @load="measure()">
                                            </template>
                                            <template x-if="!s.src">
                                                <div
                                                    class="w-full aspect-square bg-gray-200 rounded-lg grid place-items-center">
                                                    <span class="text-sm text-gray-600">Image not found</span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Arrows --}}
                            <button type="button"
                                class="absolute top-1/2 -translate-y-1/2 left-4 h-11 w-11 rounded-full bg-gray-100 shadow flex items-center justify-center text-blue-600 hover:bg-white transition"
                                @click="prev()" aria-label="Previous">
                                <span class="text-xl">&lt;</span>
                            </button>
                            <button type="button"
                                class="absolute top-1/2 -translate-y-1/2 right-4 h-11 w-11 rounded-full bg-gray-100 shadow flex items-center justify-center text-blue-600 hover:bg-white transition"
                                @click="next()" aria-label="Next">
                                <span class="text-xl">&gt;</span>
                            </button>
                        </div>

                        {{-- Dash indicators --}}
                        <div class="flex items-center justify-center gap-2 pb-4">
                            <template x-for="(s, i) in slides" :key="i">
                                <button type="button" class="h-1 w-4 rounded-sm bg-gray-300 transition-all"
                                    :class="i === current ? 'bg-gray-500 w-4' : ''" @click="go(i)"
                                    :aria-label="`Slide ${i+1}`"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Right column posts (5 columns) --}}
                    <div class="lg:col-span-5 bg-white rounded-lg shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <h3 class="text-[#007BFF] text-lg font-semibold uppercase mb-3">SPORTS BRAS | CROP TOPS</h3>
                            <p class="text-[#555]">
                                We produce made-to-order sports bras and crop tops in padded, racerback, seamless, and mesh
                                panel styles.
                                Designed for high-impact and low-impact performance, our products offer maximum comfort and
                                breathability.
                                Siatex Global supports all fabric types, size grading, and logo applications, based entirely
                                on your
                                custom specifications and tech pack instructions.
                            </p>
                        </div>
                        <div class="p-6">
                            <h3 class="text-[#007BFF] text-lg font-semibold uppercase mb-3">WINDBREAKERS | LIGHTWEIGHT
                                JACKETS</h3>
                            <p class="text-[#555]">
                                Siatex Global manufactures custom windbreakers and lightweight jackets designed for
                                sportswear,
                                streetwear, and promotional use. We offer full-zip, half-zip, and pullover styles with
                                options like
                                mesh lining, adjustable hoods, elastic cuffs, and water-resistant coatings. All features,
                                fabrics,
                                and finishes are developed precisely to your design and technical requirements.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Row Two: 3 equal cards --}}
                <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <h3 class="text-[#007BFF] text-lg font-semibold uppercase mb-3">GYM LEGGINGS | YOGA PANTS</h3>
                            <p class="text-[#555]">
                                At Siatex Global, we manufacture custom-made gym leggings and yoga pants for brands seeking
                                comfort, flexibility, and durability. Our range includes squat-proof, high-waisted,
                                seamless,
                                and compression styles. We work with moisture-wicking, recycled, and OEKO-TEX certified
                                fabrics,
                                tailored exactly to your tech packs, color preferences, and branding requirements.
                            </p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <h3 class="text-[#007BFF] text-lg font-semibold uppercase mb-3">FITNESS TEES | TANK TOPS</h3>
                            <p class="text-[#555]">
                                Siatex Global manufactures customized fitness tees and tank tops for men, women, and unisex
                                collections.
                                Styles include sleeveless, slim-fit, raglan, and vented back options. We accommodate a wide
                                range of fabric
                                choices and design elements such as reflective prints, mesh panels, and flatlock stitching –
                                produced entirely
                                as per your provided designs.
                            </p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <h3 class="text-[#007BFF] text-lg font-semibold uppercase mb-3">TRACKSUITS | JOGGERS</h3>
                            <p class="text-[#555]">
                                From lightweight training joggers to full tracksuit sets, we offer bulk OEM production using
                                cotton blends,
                                poly fleece, terry, and spandex. Clients choose every detail—fit, fabric, zipper placement,
                                drawstrings, and trims.
                                Siatex Global ensures each style aligns with your brand standards, delivering high-volume,
                                made-to-order athleticwear
                                with consistent quality and finish.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </section>

    {{-- ==== SECTION 2 (BELOW): Our Manufacturing Network (single row, no scroll) ==== --}}
    <section class="bg-[#f4f4f9] py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 container">
            <h2 class="font-oswald text-[#104f83] text-2xl sm:text-3xl font-light tracking-wide text-center mb-10">
                Our manufacturing network handles production for the brands:
            </h2>

            {{-- Single row that fits: 7 equal cards, shrink responsively --}}
            <div class="grid grid-cols-7 gap-6">
                @php
                    $logos = [
                        [
                            'src' =>
                                'https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/H%26M-Logo.svg/1024px-H%26M-Logo.svg.png',
                            'alt' => 'H&M',
                        ],
                        [
                            'src' =>
                                'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Old_Navy_Logo.svg/500px-Old_Navy_Logo.svg.png',
                            'alt' => 'Old Navy',
                        ],
                        [
                            'src' =>
                                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Hanes-logo.svg/300px-Hanes-logo.svg.png',
                            'alt' => 'Hanes',
                        ],
                        [
                            'src' => 'https://teamline.lu/wp-content/uploads/2022/02/BC-300x300.png',
                            'alt' => 'B&C Collection',
                        ],
                        ['src' => 'https://images.habeco.si/upload/files/sols_FP.png', 'alt' => "SOL'S"],
                        [
                            'src' =>
                                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Gildan_logo.svg/500px-Gildan_logo.svg.png',
                            'alt' => 'Gildan',
                        ],
                        [
                            'src' => 'https://hipfonts.com/wp-content/uploads/2023/01/Fruit_logo_cover.jpg',
                            'alt' => 'Fruit of the Loom',
                        ],
                    ];
                @endphp

                @foreach ($logos as $logo)
                    <div
                        class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-3 sm:p-4 md:p-5 flex items-center justify-center">
                        <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}"
                            class="max-h-7 sm:max-h-9 md:max-h-10 lg:max-h-12 w-auto grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition duration-300">
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Alpine carousel (Tailwind only)
        function siatexCarousel({
            slides = [],
            auto = true,
            interval = 5000
        } = {}) {
            return {
                slides,
                current: 0,
                slideWidth: 0,
                timer: null,
                init() {
                    this.$nextTick(() => this.measure());
                    window.addEventListener('resize', () => this.measure());
                    if (auto && this.slides.length > 1) this.start();
                },
                measure() {
                    const trackParent = this.$refs.track?.parentElement;
                    this.slideWidth = trackParent ? trackParent.getBoundingClientRect().width : 0;
                },
                next() {
                    this.current = (this.current + 1) % this.slides.length;
                },
                prev() {
                    this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                },
                go(i) {
                    this.current = i;
                },
                start() {
                    this.stop();
                    this.timer = setInterval(() => this.next(), interval);
                },
                stop() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },
            }
        }
    </script>
@endpush
