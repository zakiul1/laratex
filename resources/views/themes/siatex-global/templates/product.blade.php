{{-- resources/views/themes/siatex-global/templates/product.blade.php --}}
@extends('themes.siatex-global.layout')

@push('head')
    {{-- Load Poppins font + small utility class to apply it anywhere --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .font-poppins {
            font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, "Segoe UI",
                Roboto, "Helvetica Neue", Arial, "Noto Sans",
                "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
    </style>
@endpush

@section('content')
    @php
        use Illuminate\Support\Str;
        use App\Models\Product;

        // Product media
        $featuredCollection = $product->featuredMedia ?? collect();
        $imageItems = $featuredCollection
            ->map(function ($m) {
                $url =
                    method_exists($m, 'hasGeneratedConversion') && $m->hasGeneratedConversion('large')
                        ? $m->getUrl('large')
                        : $m->getUrl();
                $alt = $m->getCustomProperty('alt') ?: $m->name ?? 'Product image';
                return ['url' => $url, 'alt' => $alt, 'id' => $m->id];
            })
            ->values();

        $hasMultiple = $imageItems->count() > 1;
        $primary = $imageItems->first();

        $detailUrl = route('products.show', $product->slug ?? $product->id);
    @endphp

    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    {{-- Apply Poppins to the whole page content --}}
    <div class="bg-white py-12 relative font-poppins">
        @auth
            <div class="absolute top-4 right-4 z-10">
                <a href="{{ route('products.edit', $product) }}" class="text-xs px-3 py-3 bg-green-500 text-white">
                    Edit Product
                </a>
            </div>
        @endauth

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-700" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:underline text-gray-700">Home</a></li>
                    <li>/</li>
                    @if (isset($category) && $category?->term)
                        <li>
                            <a href="{{ route('categories.show', $category->term->slug) }}"
                                class="hover:underline text-gray-700">
                                {{ $category->term->name }}
                            </a>
                        </li>
                        <li>/</li>
                    @endif
                    <li class="font-semibold text-gray-900" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            {{-- Product Detail --}}
            <div class="bg-gray-100 p-6 md:p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    {{-- LEFT: details --}}
                    <div class="space-y-4 order-2 md:order-1">
                        <div class="w-16 h-1 bg-red-600"></div>

                        @if (isset($category) && $category?->term)
                            <h2 class="font-medium text-lg my-2 uppercase text-gray-700">
                                {{ $category->term->name }}
                            </h2>
                        @endif

                        <h1 class="text-3xl md:text-[32px] font-sans text-[#0e4f7f]">
                            {{ $product->name }}
                        </h1>

                        <div class="text-gray-700 text-justify leading-relaxed prose max-w-none">
                            @if (!empty($product->description))
                                {!! $product->description !!}
                            @elseif (!empty($product->excerpt))
                                {!! nl2br(e($product->excerpt)) !!}
                            @else
                                <p>No description available.</p>
                            @endif
                        </div>

                        <button type="button" x-data @click="$dispatch('open-order-drawer')"
                            class="bg-blue-800 hover:bg-blue-900 text-white px-6 py-3">
                            Start order
                        </button>
                    </div>

                    {{-- RIGHT: gallery (sticky, no radius/border) --}}
                    <div class="order-1 md:order-2 md:sticky md:top-4 self-start">
                        @if ($imageItems->isEmpty())
                            <div
                                class="h-[320px] md:h-[420px] xl:h-[540px] w-full bg-white flex items-center justify-center">
                                <div class="text-gray-400 text-sm">Image not available</div>
                            </div>
                        @else
                            @if (!$hasMultiple)
                                <div class="relative h-[320px] md:h-[420px] xl:h-[540px] w-full overflow-hidden ">
                                    <a href="{{ $detailUrl }}" class="block absolute inset-0">
                                        <img src="{{ $primary['url'] }}" alt="{{ $primary['alt'] }}"
                                            class="absolute inset-0 w-full h-full object-contain" loading="eager"
                                            decoding="async">
                                    </a>
                                </div>
                            @else
                                <div x-data="hfCarousel({ images: {{ $imageItems->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }} })" x-init="init()"
                                    class="relative w-full select-none group" @keydown.right.window.prevent="next()"
                                    @keydown.left.window.prevent="prev()">
                                    <div class="relative h-[320px] md:h-[420px] xl:h-[540px] w-full overflow-hidden ">
                                        <template x-for="(img, idx) in images" :key="img.id">
                                            <img x-cloak x-show="current===idx" x-transition.opacity :src="img.url"
                                                :alt="img.alt" class="absolute inset-0 w-full h-full object-contain"
                                                loading="lazy" decoding="async" @touchstart.passive="onTouchStart($event)"
                                                @touchend.passive="onTouchEnd($event)">
                                        </template>
                                    </div>

                                    <button type="button" @click="prev()"
                                        class="hidden md:flex items-center justify-center absolute top-1/2 -translate-y-1/2 -left-6 xl:-left-8 h-11 w-11 focus:outline-none"
                                        aria-label="Previous">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 19.5 8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>

                                    <button type="button" @click="next()"
                                        class="hidden md:flex items-center justify-center absolute top-1/2 -translate-y-1/2 -right-6 xl:-right-8 h-11 w-11 focus:outline-none"
                                        aria-label="Next">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 4.5 15.75 12 8.25 19.5" />
                                        </svg>
                                    </button>

                                    <div class="flex md:hidden justify-between items-center mt-2">
                                        <button @click="prev()" class="h-10 w-10 focus:outline-none" aria-label="Previous">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-auto text-gray-700"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 19.5 8.25 12l7.5-7.5" />
                                            </svg>
                                        </button>
                                        <button @click="next()" class="h-10 w-10 focus:outline-none" aria-label="Next">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-auto text-gray-700"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 4.5 15.75 12 8.25 19.5" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Extra page content (hooks) --}}
            <div class="prose max-w-none">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>

            {{-- ================= RELATED PRODUCTS (6 visible, even edge/between gaps) ================= --}}
            @php
                $currentCatSlug = isset($category) && $category?->term ? $category->term->slug : null;
                if (!$currentCatSlug && method_exists($product, 'taxonomies')) {
                    $firstTax = $product->taxonomies()->with('term')->first();
                    $currentCatSlug = optional($firstTax?->term)->slug;
                }

                $relatedItems = collect();
                if ($currentCatSlug) {
                    $related = Product::with('featuredMedia', 'taxonomies.term')
                        ->where('id', '!=', $product->id)
                        ->whereHas('taxonomies.term', function ($q) use ($currentCatSlug) {
                            $q->where('slug', $currentCatSlug);
                        })
                        ->latest()
                        ->take(24)
                        ->get();

                    $relatedItems = $related
                        ->map(function ($p) {
                            $m = $p->featuredMedia->first();
                            $img = $m
                                ? (method_exists($m, 'hasGeneratedConversion') && $m->hasGeneratedConversion('large')
                                    ? $m->getUrl('large')
                                    : $m->getUrl())
                                : null;

                            return [
                                'img' => $img,
                                'alt' => $p->name,
                                'link' => route('products.show', $p->slug ?? $p->id),
                            ];
                        })
                        ->values();
                }
            @endphp

            @if ($relatedItems->isNotEmpty())
                <section class="relative">
                    <div x-data="sixCarousel({ items: {{ $relatedItems->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }} })" x-init="init()" class="relative">

                        <button x-show="items.length > visible" @click="prev()"
                            class="hidden md:flex items-center justify-center absolute top-1/2 -translate-y-1/2 -left-6 xl:-left-8 h-11 w-11 focus:outline-none"
                            aria-label="Previous related">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Even gutter: wrapper px-2/py-2, tiles px-1 --}}
                        <div class="overflow-hidden bg-gray-100 px-1 py-3">
                            <div class="flex transition-transform duration-300 ease-out will-change-transform"
                                :style="'transform: translateX(-' + (index * (100 / visible)) + '%)'">
                                <template x-for="(it, i) in items" :key="'rel-' + i">
                                    <a :href="it.link" class="block w-1/6 shrink-0 px-2">
                                        <div
                                            class="h-[110px] md:h-[130px] xl:h-[150px] bg-white flex items-center justify-center shadow-sm ">
                                            <template x-if="it.img">
                                                <img :src="it.img" :alt="it.alt"
                                                    class="h-full w-full object-cover" loading="lazy" decoding="async">
                                            </template>
                                            <template x-if="!it.img">
                                                <div class="text-gray-400 text-xs">No image</div>
                                            </template>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <button x-show="items.length > visible" @click="next()"
                            class="hidden md:flex items-center justify-center absolute top-1/2 -translate-y-1/2 -right-6 xl:-right-8 h-11 w-11 focus:outline-none"
                            aria-label="Next related">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12 8.25 19.5" />
                            </svg>
                        </button>
                    </div>
                </section>
            @endif
            {{-- ================= /RELATED ================= --}}

            {{-- ================= TAILWIND VERSION OF YOUR MAIN/ASIDE SECTION ================= --}}

            {{-- === BEGIN: Premium Custom T-Shirts (Tailwind, matches screenshot) === --}}
            <section id="sticky-country-guide" class="font-sans leading-[1.6] text-[#333]">

                {{-- Page header (max-w 1200, p-20) --}}
                <div class="max-w-[1200px] mx-auto   pt-[24px] pb-[30px]">
                    <h1 class="font-semibold leading-[1.6] text-[#222] mb-[10px]">
                        Premium Custom T-Shirts
                    </h1>
                    <p class="text-[#555] m-0">
                        Choosing the right offshore OEM garment manufacturer for bulk custom T-shirts is about precision,
                        trust, and timing.
                        If you’re scaling in {country}, you need a partner who can translate tech packs into
                        production-ready results—on quality, on budget, and on schedule.
                    </p>
                </div>

                {{-- Main grid (8/12 article + 4/12 aside) --}}
                <div class="max-w-[1200px] mx-auto pb-[30px">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-[28px]">

                        {{-- ARTICLE --}}
                        <article class="md:col-span-8">

                            {{-- Section: Customization options --}}
                            <section class="pt-[24px]">
                                <h2 class="font-semibold text-[#222] mb-[14px]">
                                    Customization options for premium T-shirts
                                </h2>

                                <h3 class="font-semibold text-[#222] mb-[8px] text-[20px] text-[20px]">Fabrics and blends
                                </h3>
                                <ul class="list-disc pl-[20px] space-y-[4px]">
                                    <li><strong>Core cotton:</strong> 100% ringspun combed cotton…</li>
                                    <li><strong>Sustainable choices:</strong> Organic cotton, Better Cotton, recycled
                                        fibers…</li>
                                    <li><strong>Performance blends:</strong> Cotton-poly mixes, tri-blends, moisture-wicking
                                        polyester…</li>
                                    <li><strong>Specialty knits:</strong> Piqué, waffle, slub, enzyme-washed jerseys…</li>
                                </ul>
                            </section>

                            {{-- Section: MOQs (title + gray block list) --}}
                            <section class="pt-[28px]">
                                <h2 class="font-semibold text-[#222] mb-[10px]">MOQs and lead times</h2>
                                <div class="bg-[#f3f4f6] p-[16px]">
                                    <ul class="list-disc pl-[20px] space-y-[4px]">
                                        <li><strong>Typical MOQs:</strong> Screen print: 300–500 units; DTG/DTF: 50–100;
                                            custom-dye: 500–1,000+</li>
                                        <li><strong>Lead times:</strong> Proto: 7–10 days; Fit: 10–14 days; Bulk: 30–45 days
                                        </li>
                                        <li>Dye/wash effects add 5–10 days; yarn-dyed require earlier booking</li>
                                    </ul>
                                </div>
                            </section>

                            {{-- Section: Sampling --}}
                            <section class="pt-[28px]">
                                <h2 class="font-semibold text-[#222] mb-[8px]">Sampling and quality control
                                </h2>

                                <h3 class="font-semibold text-[#222] mb-[6px] text-[20px] ">Sampling roadmap</h3>
                                <ul class="list-disc pl-[20px] space-y-[4px]">
                                    <li>Tech pack alignment with BOM, measurements, Pantone codes…</li>
                                    <li>Proto, Fit, PP/PPS, Size set, strike-off/lab dips</li>
                                </ul>

                                <h3 class="font-semibold text-[#222] mt-[12px] mb-[6px] text-[20px]">In-line and final
                                    inspections</h3>
                                <ul class="list-disc pl-[20px] space-y-[4px]">
                                    <li>AQL inspections at key production stages</li>
                                    <li>Measurement/performance tests: shrinkage, twist, colorfastness, adhesion…</li>
                                    <li>Third-party QC and rework policies</li>
                                </ul>
                            </section>

                            {{-- Section: Certifications (gray block) --}}
                            <section class="pt-[28px]">
                                <h2 class="font-semibold text-[#222] mb-[10px]">Certifications and ethical
                                    practices</h2>
                                <div class="bg-[#f3f4f6] p-[16px]">
                                    <ul class="list-disc pl-[20px] space-y-[4px]">
                                        <li>OEKO-TEX Standard 100, BSCI/SEDEX, WRAP, ISO 9001/14001 on request</li>
                                        <li>GOTS, GRS with chain-of-custody when specified</li>
                                        <li>No child/forced labor, safe conditions, fair wages</li>
                                        <li>Sustainability: water-based inks, low-impact dyes, recycling initiatives</li>
                                    </ul>
                                </div>
                            </section>

                            {{-- Section: Payment & shipping --}}
                            <section class="pt-[28px]">
                                <h2 class="font-semibold text-[#222] mb-[10px]">Payment terms and global
                                    shipping</h2>

                                <h3 class="font-semibold text-[#222] mb-[6px] text-[20px]">Payment and terms</h3>
                                <ul class="list-disc pl-[20px] space-y-[4px]">
                                    <li>30% deposit, 70% balance before shipment</li>
                                    <li>Samples paid in full; PayPal/card for small orders</li>
                                    <li>EXW, FOB, CIF, DDP; USD/EUR quotes</li>
                                </ul>

                                <h3 class="font-semibold text-[#222] mt-[12px] mb-[6px] text-[20px]">Shipping and logistics
                                </h3>
                                <ul class="list-disc pl-[20px] space-y-[4px]">
                                    <li>Courier, air, sea, or rail options</li>
                                    <li>Documentation: invoice, packing list, HS codes, CO, test reports</li>
                                    <li>Custom carton specs, compliance labeling, palletization</li>
                                </ul>
                            </section>

                            {{-- Section: Tips (gray block) --}}
                            <section class="pt-[28px]">
                                <h2 class="font-semibold text-[#222] mb-[10px]">
                                    Tips for sourcing bulk custom T-shirts
                                </h2>
                                <div class="bg-[#f3f4f6] p-[16px]">
                                    <ul class="list-disc pl-[20px] space-y-[4px]">
                                        <li>Define must-haves, lock tech packs early</li>
                                        <li>Pilot before scaling; approve golden samples</li>
                                        <li>Plan calendars backward from launch date</li>
                                        <li>Negotiate color/fabric consolidation</li>
                                        <li>Budget for testing; factor total landed cost</li>
                                    </ul>
                                </div>
                            </section>

                            {{-- Section: 10 reasons --}}
                            <section class="pt-[28px] pb-[20px]">
                                <h2 class="font-semibold text-[#222] mb-[12px]">
                                    10 reasons to choose our company
                                </h2>
                                <ol class="list-decimal pl-[20px] space-y-[6px]">
                                    <li>OEM expertise for global brands</li>
                                    <li>Full customization from fabric to packaging</li>
                                    <li>Scale-friendly MOQs</li>
                                    <li>Reliable lead times with milestone tracking</li>
                                    <li>Certification access for quality and sustainability</li>
                                    <li>Sustainable materials and processes</li>
                                    <li>Retail-grade QC standards</li>
                                    <li>Transparent pricing</li>
                                    <li>Global logistics capabilities</li>
                                    <li>Dedicated, responsive support</li>
                                </ol>
                            </section>

                        </article>

                        {{-- ASIDE (sticky) --}}
                        <aside class="md:col-span-4 md:sticky md:top-[20px] h-fit">
                            <div class="bg-[#f5f7fa] shadow-sm rounded-[10px] p-[20px]">
                                <h3 class="font-semibold text-[#222] mb-[12px] text-[20px]">About Our Company</h3>
                                <p class="mb-[14px]">
                                    We are a trusted OEM garment manufacturer delivering high-quality, custom apparel
                                    worldwide for over 15 years.
                                </p>

                                <h4 class="font-semibold text-[#222] mb-[8px]">Contact Us</h4>

                                <div class="space-y-[10px]">
                                    <p class="m-0"><span class="font-semibold">Address:</span> Niketon, Gulshan-1,
                                        Dhaka - 1212</p>
                                    <p class="m-0"><span class="font-semibold">Phone:</span> (02) 222-285-548</p>
                                    <p class="m-0"><span class="font-semibold">Email:</span> sales@siatex.com </p>
                                    <p class="m-0"><span class="font-semibold">Website:</span> www.siatex.com</p>
                                </div>
                            </div>
                        </aside>

                    </div>
                </div>

                {{-- Page footer --}}

            </section>

            {{-- === END === --}}

            {{-- ================= /TAILWIND MAIN/ASIDE ================= --}}
        </div>
    </div>

    @include('themes.siatex-global.partials.order-drawer', ['product' => $product])

    <script>
        function hfCarousel({
            images
        }) {
            return {
                images,
                current: 0,
                startX: null,
                init() {
                    if (!Array.isArray(this.images)) this.images = [];
                    this.current = 0;
                    this.preload(this.current + 1);
                },
                go(i) {
                    if (!this.images.length) return;
                    this.current = (i + this.images.length) % this.images.length;
                    this.preload(this.current + 1);
                },
                next() {
                    this.go(this.current + 1);
                },
                prev() {
                    this.go(this.current - 1);
                },
                onTouchStart(e) {
                    this.startX = e.changedTouches?.[0]?.screenX ?? null;
                },
                onTouchEnd(e) {
                    if (this.startX == null) return;
                    const endX = e.changedTouches?.[0]?.screenX ?? this.startX;
                    const d = endX - this.startX;
                    if (Math.abs(d) > 40) {
                        d < 0 ? this.next() : this.prev();
                    }
                    this.startX = null;
                },
                preload(i) {
                    const idx = (i + this.images.length) % this.images.length;
                    const img = new Image();
                    img.src = this.images[idx]?.url ?? '';
                }
            }
        }

        function sixCarousel({
            items
        }) {
            return {
                items,
                visible: 6,
                index: 0,
                max: 0,
                init() {
                    if (!Array.isArray(this.items)) this.items = [];
                    this.max = Math.max(0, this.items.length - this.visible);
                    this.index = 0;
                },
                next() {
                    if (this.index < this.max) this.index++;
                },
                prev() {
                    if (this.index > 0) this.index--;
                },
            }
        }
    </script>
@endsection
