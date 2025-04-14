@extends('layouts.dashboard')
@section('content')
    <h2 class="text-xl font-semibold mb-4">Edit Category</h2>
    @include('categories.form', ['category' => $category])
@endsection