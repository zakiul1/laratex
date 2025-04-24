{{-- resources/views/errors/405.blade.php --}}
@extends('layouts.app') {{-- or whatever your main layout is --}}

@section('title', 'Method Not Allowed')
@section('code', '405')

@section('content')
    <div class="max-w-xl mx-auto text-center py-16">
        <h1 class="text-6xl font-bold mb-4">405</h1>
        <p class="text-xl mb-6">Sorry, that HTTP method is not allowed on this URL.</p>
        <a href="{{ url()->previous() }}" class="underline">Go Back</a>
    </div>
@endsection
