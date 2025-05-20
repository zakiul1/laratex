@extends('layouts.dashboard')

@section('content')
    <div class=" mx-auto">
        <h1 class="text-2xl font-bold mb-6">New Post Category</h1>
        @php $isEdit = false; @endphp
        @include('admin.post-taxonomies.form')
    </div>
@endsection
