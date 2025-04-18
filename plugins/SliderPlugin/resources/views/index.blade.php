@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-bold mb-4">All Sliders</h1>

    @foreach ($sliders as $slider)
        <div class="p-4 bg-white shadow rounded mb-2">
            <h2>{{ $slider->title }}</h2>
        </div>
    @endforeach
@endsection