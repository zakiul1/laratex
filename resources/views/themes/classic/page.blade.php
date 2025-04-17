@extends('themes.classic.layout') {{-- assuming a classic theme layout exists --}}

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-4">{{ $page->title }}</h1>

        <div class="prose max-w-none">
            {!! nl2br(e($page->content)) !!}
        </div>
    </div>
@endsection