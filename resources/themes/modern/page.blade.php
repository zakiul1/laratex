@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-semibold mb-2">{{ $post->title }}</h1>
        <div class="prose">
            {!! $post->content !!}
        </div>
    </div>
@endsection