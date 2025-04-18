@extends('layouts.dashboard')

@section('content')
    <h2 class="text-xl font-bold mb-4">Settings for {{ $plugin->name }}</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin->slug) }}" class="space-y-4 max-w-lg">
        @csrf

        @foreach($fields as $key => $field)
            <div>
                <label class="block text-sm font-medium">{{ $field['label'] }}</label>

                @php
                    $value = $saved[$key] ?? $field['default'] ?? '';
                @endphp

                @if($field['type'] === 'text')
                    <input type="text" name="{{ $key }}" value="{{ old($key, $value) }}" class="w-full border rounded p-2 mt-1">
                @elseif($field['type'] === 'textarea')
                    <textarea name="{{ $key }}" class="w-full border rounded p-2 mt-1" rows="3">{{ old($key, $value) }}</textarea>
                @elseif($field['type'] === 'checkbox')
                    <input type="checkbox" name="{{ $key }}" value="1" {{ $value ? 'checked' : '' }} class="mt-1">
                @endif
            </div>
        @endforeach

        <button class="bg-blue-600 text-white px-4 py-2 rounded">Save Settings</button>
    </form>
@endsection