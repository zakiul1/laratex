@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto mt-6 bg-white dark:bg-neutral-900 shadow rounded p-6">
    <h2 class="text-xl font-semibold mb-4">All Menus</h2>

    @if($menus->count())
        <form method="POST" action="{{ route('menus.select') }}">
            @csrf
            <label for="menuSelect" class="text-sm font-medium">Select a menu to edit:</label>
            <div class="flex items-center gap-2 mt-2">
                <select name="menu_id" id="menuSelect" class="w-full border-gray-300 rounded">
                    @foreach($menus as $menu)
                        <option value="{{ $menu->id }}">{{ $menu->name }} ({{ ucfirst($menu->location) }})</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                    Select
                </button>
                <a href="{{ route('menus.create') }}" class="text-sm text-blue-600 hover:underline">or create a new menu</a>
            </div>
        </form>
    @else
        <p class="text-gray-500">No menus found. <a href="{{ route('menus.create') }}" class="text-blue-600 hover:underline">Create your first menu</a>.</p>
    @endif
</div>
@endsection
