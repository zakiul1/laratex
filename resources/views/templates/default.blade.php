@extends('layouts.app')

@section('content')
    <section class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold mb-6">{{ $page->title }}</h1>
            {!! $page->content !!}
        </div>
    </section>
@endsection