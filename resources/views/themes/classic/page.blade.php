@extends('themes.classic.layout') {{-- assuming a classic theme layout exists --}}

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1
            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight sm:tracking-wide font-oswald mx-auto text-center mb-4">
            {{ $page->title }}
        </h1>

        <div class="prose prose-sm sm:prose lg:prose-lg max-w-none text-justify">
            {{--   {!! $pageOutput !!} --}}
            {!! apply_filters('the_content', $pageOutput) !!}
        </div>
    </div>
@endsection
