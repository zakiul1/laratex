@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-bold mb-4">Add New Slider</h1>

    <form method="POST" action="{{ route('slider-plugin.sliders.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium">Title</label>
            <input type="text" name="title" class="w-full border rounded p-2">
        </div>

        <button class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
    </form>
@endsection