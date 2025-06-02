@extends('layout')

@section('content')
    @php
        $finder = view()->getFinder();
        $hints = $finder->getHints();
    @endphp

    @if (array_key_exists('slider-plugin-front', $hints) && view()->exists('slider-plugin-front::slider'))
        @include('slider-plugin-front::slider')
    @endif





    {{-- Page Content --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
        {!! apply_filters('the_content', $pageOutput) !!}
    </div>

    {{-- Featured Products --}}
@endsection
