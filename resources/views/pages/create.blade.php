@extends('layouts.dashboard')

@section('content')
    @include('pages.form', [
        'page' => $page ?? new \App\Models\Post(['type' => 'page']),
        'templates' => $templates,
        // on create you'll pass in $initialImage as empty or from your controller
        'initialImage' => $initialImage ?? '',
    ])
@endsection
