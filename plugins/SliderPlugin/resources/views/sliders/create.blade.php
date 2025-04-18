@extends('layouts.dashboard')

@section('content')
    <div class=" mx-auto p-6 bg-white dark:bg-gray-900 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Create New Slider</h2>
        @include('slider-plugin::sliders._form')
    </div>
@endsection
