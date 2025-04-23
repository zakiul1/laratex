{{-- resources/views/themes/{{ $theme }}/partials/featured-categories.blade.php --}}

@php
    $count = $categories->count();
@endphp

@if ($count)
    <section class="py-10">
        <div class="container mx-auto px-4">


            @if ($count > 3)
                {{-- Carousel for more than 3 categories --}}
                <div x-data="{
                    cats: {{ json_encode(
                        $categories->map(
                                fn($c) => [
                                    'imgSrc' => asset('storage/' . $c->featured_image),
                                    'imgAlt' => $c->name,
                                    'title' => $c->name,
                                    'url' => route('categories.show', $c->slug),
                                ],
                            )->toArray(),
                    ) }},
                    idx: 0,
                    prev() { this.idx = (this.idx - 1 + this.cats.length) % this.cats.length },
                    next() { this.idx = (this.idx + 1) % this.cats.length },
                    visible() {
                        return Array.from({ length: 3 }, (_, i) =>
                            this.cats[(this.idx + i) % this.cats.length]
                        );
                    }
                }" class="relative w-full overflow-hidden">
                    {{-- Previous Button --}}
                    <button @click="prev()"
                        class="absolute left-6 top-1/2 z-20 flex items-center justify-center
         w-14 h-14 bg-white/20 text-white rounded-full
         shadow-lg transition duration-300 ease-out focus:outline-none
         hover:bg-white/20 hover:backdrop-blur-sm"
                        aria-label="Previous Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    {{-- Next Button --}}
                    <button @click="next()"
                        class="absolute right-6 top-1/2 z-20 flex items-center justify-center
         w-14 h-14 bg-white/10 text-white rounded-full
         shadow-lg transition duration-300 ease-out focus:outline-none
         hover:bg-white/30 hover:backdrop-blur-md"
                        aria-label="Next Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>


                    {{-- Slide Row: always show exactly 3 items --}}
                    <div class="flex transition-transform duration-300">
                        <template x-for="cat in visible()" :key="cat.url">
                            <a :href="cat.url" class="flex-1 block overflow-hidden rounded-lg shadow-lg mx-2">
                                <img :src="cat.imgSrc" :alt="cat.imgAlt" class="w-full h-auto object-cover" />
                                <div class="p-4 text-center">
                                    <h3 class="font-semibold" x-text="cat.title"></h3>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            @else
                {{-- Static grid for 1–3 categories --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}"
                            class="block overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition">
                            <img src="{{ asset('storage/' . $category->featured_image) }}" alt="{{ $category->name }}"
                                class="w-full h-auto object-cover" />
                            <div class="p-4 text-center">
                                <h3 class="font-semibold">{{ $category->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </section>
@endif
