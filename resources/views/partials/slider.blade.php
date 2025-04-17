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