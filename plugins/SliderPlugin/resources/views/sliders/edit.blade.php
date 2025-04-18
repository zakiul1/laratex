@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-900 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Edit Slider</h2>
        {{-- Pass the model so the partial can fill existing data --}}
        @include('slider-plugin::sliders._form', ['slider' => $slider])
    </div>
@endsection
