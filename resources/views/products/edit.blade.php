{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-2xl font-semibold mb-4">Edit Product</h1>
        @include('products.form')
    </div>
@endsection
