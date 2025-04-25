{{-- resources/views/themes/{{ $theme }}/partials/featured-categories.blade.php --}}

@php
    $count = $categories->count();
@endphp

@if ($count)
    <section class="py-10">
        <div class="container mx-auto px-4">

            @if ($count > 3)
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
                    <!-- Prev Button -->
                    <button @click="prev()"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 flex items-center justify-center
                               w-10 h-10 sm:w-14 sm:h-14 bg-white/20 text-white rounded-full
                               shadow-lg transition duration-300 ease-out focus:outline-none
                               hover:bg-white/30"
                        aria-label="Previous Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-6 sm:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Next Button -->
                    <button @click="next()"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 flex items-center justify-center
                               w-10 h-10 sm:w-14 sm:h-14 bg-white/20 text-white rounded-full
                               shadow-lg transition duration-300 ease-out focus:outline-none
                               hover:bg-white/30"
                        aria-label="Next Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-6 sm:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Slide Row: always 3 visible -->
                    <div class="flex transition-transform duration-300">
                        <template x-for="cat in visible()" :key="cat.url">
                            <a :href="cat.url"
                                class="flex-1 relative block overflow-hidden rounded-lg shadow-lg mx-1 sm:mx-2">
                                <img :src="cat.imgSrc" :alt="cat.imgAlt"
                                    class="w-full h-32 sm:h-48 md:h-auto lg:h-auto object-cover" />
                                <div class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 py-2">
                                    <h3 class="font-semibold text-white text-center text-sm sm:text-base md:text-[42px] py-3 uppercase font-[oswald]"
                                        x-text="cat.title"></h3>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}"
                            class="block overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition mx-1 sm:mx-0">
                            <img src="{{ asset('storage/' . $category->featured_image) }}" alt="{{ $category->name }}"
                                class="w-full h-32 sm:h-48 object-cover" />
                            <div class="p-2 sm:p-4 text-center">
                                <h3 class="font-semibold text-sm sm:text-base md:text-lg">{{ $category->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </section>
@endif
