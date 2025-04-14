@extends('layouts.dashboard')

@section('content')
<div class="max-w-3xl mx-auto mt-6 bg-white dark:bg-neutral-900 p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Create New Menu</h2>

    <!-- ✅ Success Message -->
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- ✅ Validation Error Messages -->
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded space-y-1">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('menus.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium">Menu Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="mt-1 w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-300 dark:bg-neutral-800 dark:text-white">
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Menu Settings</label>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="auto_add_pages" id="auto_add_pages" class="rounded"
                       {{ old('auto_add_pages') ? 'checked' : '' }}>
                <label for="auto_add_pages" class="text-sm">Automatically add new top-level pages to this menu</label>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-medium">Display location:</p>
                <div class="flex gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="location" value="header" class="rounded"
                               {{ old('location') === 'header' ? 'checked' : '' }}>
                        Header
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="location" value="footer" class="rounded"
                               {{ old('location') === 'footer' ? 'checked' : '' }}>
                        Footer
                    </label>
                </div>
            </div>
        </div>

        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm">
            Create Menu
        </button>
    </form>
</div>
@endsection
