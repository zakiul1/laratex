{{-- resources/views/themes/my‑theme/post.blade.php --}}
{{-- @extends('themes.' . getActiveTheme() . '.themes.classic.layout') --}}
@extends('themes.siatexbd.layout')

@section('content')
    <article class=" prose lg:prose-xl mx-auto">
        <h1>{{ $post->title }}</h1>
        <p class="text-sm text-gray-500">Published on {{ $post->created_at->format('M j, Y') }}</p>

        {{-- If you’re using the block‐builder for posts, render that --}}
        @if (isset($postOutput))
            {!! $postOutput !!}
        @else
            {{-- otherwise just dump the raw content --}}
            {!! $post->content !!}
        @endif
    </article>
@endsection
