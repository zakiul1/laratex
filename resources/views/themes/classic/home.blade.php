@extends('layout')

@section('content')
    <div class="">
        {!! apply_filters('slider.header', '') !!}
    </div>
    <section class="bg-[#bf0b2c] py-2 md:py-4">
        <div class="container mx-auto px-4 flex flex-row justify-around items-center text-white space-x-4 overflow-x-auto">

            <!-- Money Back Guarantee -->
            <div class="flex flex-col items-center text-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 1.75l6.16 3.42v5.66c0 4.05-2.53 7.83-6.16 9.33
                                                                                                                                                                                                                                                                 c-3.63-1.5-6.16-5.28-6.16-9.33V5.17L12 1.75z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2l4-4" />
                </svg>
                <span class="font-medium text-xs md:text-sm">Money Back Guarantee</span>
            </div>

            <!-- Easy Returns -->
            <div class="flex flex-col items-center space-y-2 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 7v5h5m0 0l-2-2m2 2l-2 2M21 17v-5h-5m0 0l2 2m-2-2l2-2" />
                </svg>
                <span class="font-medium text-xs md:text-sm">Easy Returns</span>
            </div>

            <!-- Customer Support 24/7 -->
            <div class="flex flex-col items-center space-y-2 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 8h10M7 12h4m-9 8v-2a2 2 0 0 1 2-2h1
                                                                                                                                                                                                                                                                 a10 10 0 0 1 10-10V5a2 2 0 0 1 2-2h.01
                                                                                                                                                                                                                                                                 A2 2 0 0 1 22 5v9a2 2 0 0 1-2 2h-3l-4 4v-4H7z" />
                </svg>
                <span class="font-medium text-xs md:text-sm">Customer Support 24/7</span>
            </div>

        </div>
    </section>





    {{-- Category Section View --}}


    {{-- Featured Products Section --}}
    {{-- 1) Any block-builder / page output you have --}}

    <div class="container mx-auto ">
        {!! apply_filters('the_content', $pageOutput) !!}
    </div>



    <div class="container mx-auto px-4 py-8">
        {{-- Featured Products --}}
        @if ($featuredProducts->isNotEmpty())
            <h2 class="text-3xl font-bold mb-8 text-center">
                {{ $featuredCategory->term->name }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($featuredProducts as $fp)
                    @php $fm = $fp->featuredMedia->first(); @endphp

                    @foreach ($featuredProducts as $fp)
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

                            {{-- Stars (empty) --}}
                            <div class="flex mb-4">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-gray-300 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1
                                         1 0 00.95.69h4.162c.969 0 1.371 1.24.588
                                         1.81l-3.37 2.455a1 1 0 00-.364 1.118l1.287
                                         3.966c.3.921-.755 1.688-1.54
                                         1.118l-3.37-2.455a1 1 0 00-1.175
                                         0l-3.37 2.455c-.784.57-1.84-.197-1.54-1.118l1.287-
                                         3.966a1 1 0 00-.364-1.118L2.063
                                         9.393c-.783-.57-.38-1.81.588-1.81h4.162a1
                                         1 0 00.95-.69l1.286-3.966z" />
                                    </svg>
                                @endfor
                            </div>

                            {{-- Read More as a visual cue --}}
                            <span
                                class="mt-auto inline-block text-center bg-black text-white px-4 py-2 rounded shadow hover:bg-gray-800 transition">
                                READ MORE
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 py-8">
                No Featured Products found.
            </p>
        @endif
    </div>




@endsection













@push('scripts')
@endpush
