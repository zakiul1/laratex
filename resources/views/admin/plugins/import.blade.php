@extends('layouts.dashboard')

@section('content')
    <h2 class="text-xl font-bold mb-4">Import Plugin (.zip)</h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.plugins.import.upload') }}" enctype="multipart/form-data"
        class="bg-white p-6 rounded shadow max-w-xl">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium">Plugin ZIP File</label>
            <input type="file" name="plugin_zip" accept=".zip" required
                class="w-full mt-2 border border-gray-300 p-2 rounded">
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">Upload Plugin</button>
    </form>
@endsection