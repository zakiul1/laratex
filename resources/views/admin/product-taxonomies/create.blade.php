{{-- resources/views/admin/product-taxonomies/create.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-2xl font-semibold mb-4">Add New Product Category</h1>
        @include('admin.product-taxonomies.form')
    </div>
@endsection
