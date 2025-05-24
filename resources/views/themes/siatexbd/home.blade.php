@extends('layout')

@section('content')
    <div class="">
        {!! apply_filters('slider.header', '') !!}
    </div>


    {{-- Page Content --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
        {!! apply_filters('the_content', $pageOutput) !!}
    </div>

    {{-- Featured Products --}}
    @include('partials.dynamicgrid-cart-scripts')
@endsection
