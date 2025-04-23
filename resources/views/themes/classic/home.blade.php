@extends('layout')

@section('content')
    <div class="">
        {!! apply_filters('slider.header', '') !!}
    </div>
    <section class="bg-[#bf0b2c] py-3">
        <div class="container mx-auto flex justify-around  text-white">

            <!-- Money Back Guarantee -->
            <div class="flex flex-col items-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 1.75l6.16 3.42v5.66c0 4.05-2.53 7.83-6.16 9.33
                                                                                                                                     c-3.63-1.5-6.16-5.28-6.16-9.33V5.17L12 1.75z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2l4-4" />
                </svg>
                <span class="font-medium">Money Back Guarantee</span>
            </div>

            <!-- Easy Returns -->
            <div class="flex flex-col items-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 7v5h5m0 0l-2-2m2 2l-2 2M21 17v-5h-5m0 0l2 2m-2-2l2-2" />
                </svg>
                <span class="font-medium">Easy Returns</span>
            </div>

            <!-- Customer Support 24/7 -->
            <div class="flex flex-col items-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 8h10M7 12h4m-9 8v-2a2 2 0 0 1 2-2h1
                                                                                                                                     a10 10 0 0 1 10-10V5a2 2 0 0 1 2-2h.01
                                                                                                                                     A2 2 0 0 1 22 5v9a2 2 0 0 1-2 2h-3l-4 4v-4H7z" />
                </svg>
                <span class="font-medium">Customer Support 24/7</span>
            </div>

        </div>
    </section>



    {{-- Category Section View --}}


    {{-- Featured Products Section --}}
    {{-- 1) Any block-builder / page output you have --}}

    <div class="container mx-auto ">
        {!! apply_filters('the_content', $pageOutput) !!}
    </div>
@endsection













@push('scripts')
@endpush
