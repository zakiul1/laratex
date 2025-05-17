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
@endsection
