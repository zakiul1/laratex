@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">{{ $category->name }}</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div class="bg-white p-4 shadow rounded">
                    <img src="{{ asset('storage/' . $product->featured_image) }}" class="w-full h-40 object-cover rounded mb-2">
                    <h2 class="text-lg font-semibold">{{ $product->name }}</h2>
                </div>
            @endforeach
        </div>
    </div>
@endsection