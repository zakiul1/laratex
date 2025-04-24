@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        @php
            // on create, we want a blank Post instance
            $post = $post ?? new \App\Models\Post(['type' => 'post']);

            // pull in any templates
            $templates = $templates ?? getThemeTemplates();

            // preview for any old upload (none yet on create)
            $initialImage = old('featured_image') ? asset('storage/' . old('featured_image')) : '';
        @endphp

        @include('posts.form', compact('post', 'templates', 'initialImage'))
    </div>
@endsection
