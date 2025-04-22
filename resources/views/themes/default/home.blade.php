@extends('themes.default.layout')

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center space-y-6">
        <h1 class="text-4xl font-bold text-gray-700">No Theme Active</h1>
        <p class="text-gray-600">
            It looks like you haven’t activated a front‑end theme yet.
            Please go to your admin panel and activate one.
        </p>
        <a href="{{ route('themes.index') }}"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Go to Theme Settings
        </a>
    </div>
@endsection
