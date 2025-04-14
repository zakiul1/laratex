@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        @include('posts.form', ['post' => $post])
    </div>

@endsection