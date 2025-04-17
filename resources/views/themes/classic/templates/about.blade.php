@extends('themes.classic.layout')

@section('title', $page->meta_title ?? 'About Us')

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-6">About Us</h1>

        @if ($page->featured_image)
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="About Image" class="w-full mb-6 rounded shadow">
        @endif

        <div class="prose max-w-none">
            {!! nl2br(e($page->content)) !!}
        </div>
    </div>
@endsection