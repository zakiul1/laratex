@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-semibold mb-4">{{ $page->title }}</h2>

        <div class="prose max-w-none">
            {!! nl2br(e($page->content)) !!}
        </div>

        <div class="mt-6 text-sm text-gray-500">
            <p><strong>Slug:</strong> {{ $page->slug }}</p>
            <p><strong>Status:</strong> {{ $page->status ? 'Active' : 'Inactive' }}</p>
            <p><strong>Meta Title:</strong> {{ $page->meta_title }}</p>
            <p><strong>Meta Description:</strong> {{ $page->meta_description }}</p>
        </div>

        <a href="{{ route('pages.edit', $page->id) }}" class="mt-6 inline-block text-sm text-blue-600 hover:underline">
            Edit this Page
        </a>
    </div>
@endsection