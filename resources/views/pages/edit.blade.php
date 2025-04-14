@extends('layouts.dashboard')

@section('content')
    @include('pages.form', ['page' => $page])
@endsection