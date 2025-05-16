@extends('layout')

@section('content')
    <div class="">
        {!! apply_filters('slider.header', '') !!}
    </div>
    <section class="bg-[#bf0b2c] py-2 md:py-4">
        <div class="container mx-auto px-4 flex flex-row justify-around items-center text-white space-x-4 overflow-x-auto">
            <!-- Money Back Guarantee -->
            <div class="flex flex-col items-center text-center space-y-2">
                <!-- svg… -->
                <span class="font-medium text-xs md:text-sm">Money Back Guarantee</span>
            </div>
            <!-- Easy Returns -->
            <div class="flex flex-col items-center space-y-2 text-center">
                <!-- svg… -->
                <span class="font-medium text-xs md:text-sm">Easy Returns</span>
            </div>
            <!-- Customer Support 24/7 -->
            <div class="flex flex-col items-center space-y-2 text-center">
                <!-- svg… -->
                <span class="font-medium text-xs md:text-sm">Customer Support 24/7</span>
            </div>
        </div>
    </section>

    {{-- Page Content --}}
    <div class="container mx-auto ">
        {!! apply_filters('the_content', $pageOutput) !!}
    </div>

    {{-- Featured Products --}}
    <div class="container mx-auto px-4 py-8">
        @if ($featuredProducts->isNotEmpty())
            <h2 class="text-3xl font-bold mb-8 text-center">
                {{ $featuredCategory->term->name }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($featuredProducts->unique('id') as $fp)
                    @php $fm = $fp->featuredMedia->first(); @endphp

                    <a href="{{ route('products.show', $fp->slug) }}"
                        class="block bg-white rounded-lg shadow p-6 flex flex-col hover:shadow-lg transition">
                        {{-- Image --}}
                        @if ($fm)
                            <div class="w-full aspect-w-1 aspect-h-1 rounded overflow-hidden mb-4">
                                <x-responsive-image :media="$fm" class="w-full h-full object-cover"
                                    alt="{{ $fp->name }}" />
                            </div>
                        @endif

                        {{-- Title --}}
                        <h3 class="text-lg font-bold mb-2">
                            {{ $fp->name }}
                        </h3>

                        {{-- Stars (static) --}}
                        <div class="flex mb-4">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 text-gray-300 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049…" />
                                </svg>
                            @endfor
                        </div>

                        {{-- Read More CTA --}}
                        <span
                            class="mt-auto inline-block text-center bg-black text-white px-4 py-2 rounded shadow hover:bg-gray-800 transition">
                            READ MORE
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 py-8">
                No Featured Products found.
            </p>
        @endif
    </div>
@endsection
