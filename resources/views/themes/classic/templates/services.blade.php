@extends('themes.classic.layout')

@section('title', $page->meta_title ?? 'Our Services')

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-6">Our Services</h1>

        @if ($page->featured_image)
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="Services" class="w-full mb-6 rounded shadow">
        @endif

        <div class="prose max-w-none">
            {!! $pageOutput !!}
        </div>
    </div>
@endsection
