@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        @php
            // $post is injected via route-model binding
            $templates = $templates ?? getThemeTemplates();

            // show either the newly‐chosen file or existing featured image
            $initialImage = old('featured_image')
                ? asset('storage/' . old('featured_image'))
                : (isset($post->featured_image)
                    ? asset('storage/' . $post->featured_image)
                    : '');
        @endphp

        @include('posts.form', compact('post', 'templates', 'initialImage'))
    </div>
@endsection
