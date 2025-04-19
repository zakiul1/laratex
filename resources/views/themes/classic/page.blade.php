@extends('themes.classic.layout') {{-- assuming a classic theme layout exists --}}

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class=" container mx-auto px-4 py-8">
        <h1 class="text-3xl mx-auto text-center font-bold mb-4">{{ $page->title }}</h1>

        <div class="prose max-w-none text-justify">
            {!! $pageOutput !!}

        </div>
    </div>
@endsection
