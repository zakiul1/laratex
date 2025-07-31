@extends('themes.classic.layout')

@section('title', $page->meta_title ?? 'About Us')

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl text-center font-bold mb-6">About Us</h1>

        @if ($page->featured_image)
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="About Image"
                class="w-full sm:w-3/4 lg:w-1/2 mx-auto mb-6 rounded-lg shadow-lg">
        @endif

        <div class="prose prose-sm sm:prose md:prose-lg lg:prose-xl max-w-none">
            {!! $pageOutput !!}
        </div>
    </div>
@endsection
