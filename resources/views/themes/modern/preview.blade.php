@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-12 text-center">
        <h1 class="text-3xl font-bold">Welcome to the {{ ucfirst($theme) }} Theme</h1>
        <p class="mt-4 text-gray-600">This is a live preview page for demonstration.</p>
    </div>
@endsection
