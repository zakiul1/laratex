@extends('layouts.dashboard')

@section('content')
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Plugins</h1>

                <a href="{{ route('admin.plugins.import.form') }}"
                    class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    + Import Plugin
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- 🔔 Auto-disabled warning --}}
            @php
    $disabledDueToDependency = [];

    foreach ($plugins as $plugin) {
        if (!$plugin->enabled) {
            $jsonPath = base_path("plugins/{$plugin->slug}/plugin.json");

            if (File::exists($jsonPath)) {
                $meta = json_decode(File::get($jsonPath), true);
                if (!empty($meta['requires'])) {
                    foreach ($meta['requires'] as $requiredSlug) {
                        $required = $plugins->firstWhere('slug', $requiredSlug);
                        if (!$required || !$required->enabled) {
                            $disabledDueToDependency[] = $plugin->name;
                            break;
                        }
                    }
                }
            }
        }
    }
            @endphp

            @if (count($disabledDueToDependency))
                <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
                    ⚠ The following plugins were auto-disabled due to unmet dependencies:
                    <strong>{{ implode(', ', $disabledDueToDependency) }}</strong>
                </div>
            @endif

            <div class="space-y-4">
                @foreach ($plugins as $plugin)
                                        @php
                    $jsonPath = base_path("plugins/{$plugin->slug}/plugin.json");
                    $meta = File::exists($jsonPath) ? json_decode(File::get($jsonPath), true) : [];
                    $missingDeps = [];

                    if (!empty($meta['requires'])) {
                        foreach ($meta['requires'] as $requiredSlug) {
                            $required = $plugins->firstWhere('slug', $requiredSlug);
                            if (!$required || !$required->enabled) {
                                $missingDeps[] = $requiredSlug;
                            }
                        }
                    }

                    $hasNewVersion = isset($meta['version']) && $meta['version'] !== $plugin->version;
                    $remoteUpdate = $updates[$plugin->slug] ?? null;
                                        @endphp

                                        <div class="p-4 bg-white rounded shadow flex justify-between items-center">
                                            <div>
                                                <h2 class="text-lg font-semibold">
                                                    {{ $plugin->name }}
                                                    <span class="text-sm text-gray-500">v{{ $plugin->version }}</span>
                                                    @if ($remoteUpdate)
                                                        <span class="text-sm text-yellow-600 ml-2">
                                                            ⬆ Update: v{{ $remoteUpdate['version'] }}
                                                        </span>
                                                    @endif
                                                </h2>

                                                <p class="text-sm text-gray-600">{{ $plugin->description }}</p>

                                                @if (count($missingDeps))
                                                    <p class="text-sm text-red-600 mt-1">
                                                        ⚠ Requires: {{ implode(', ', $missingDeps) }}
                                                    </p>
                                                @endif

                                                @if ($remoteUpdate && !empty($remoteUpdate['changelog']))
                                                    <p class="text-xs text-gray-500 mt-1 italic">
                                                        {{ $remoteUpdate['changelog'] }}
                                                    </p>
                                                @endif

                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <a href="{{ route('admin.plugins.export', $plugin->slug) }}"
                                                        class="text-sm text-blue-600 underline hover:text-blue-800">
                                                        Export Plugin
                                                    </a>

                                                    <a href="{{ route('admin.plugins.settings.edit', $plugin->slug) }}"
                                                        class="text-sm text-gray-600 underline hover:text-gray-800">
                                                        Settings
                                                    </a>

                                                    @if ($remoteUpdate)
                                                        <form action="{{ route('admin.plugins.update', $plugin->slug) }}" method="POST"
                                                            onsubmit="return confirm('Update plugin {{ $plugin->name }} to v{{ $remoteUpdate['version'] }}?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="text-sm bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">
                                                                ⬆ Update Now
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                         {{--    Action Buttons --}}
                                            <div class="flex items-center gap-4">
                                                <form action="{{ route('admin.plugins.toggle', $plugin->id) }}" method="POST">
                                                    @csrf
                                                    <button
                                                        class="px-4 py-1 rounded text-sm font-medium 
                                                                                            {{ $plugin->enabled ? 'bg-red-500 text-white' : 'bg-green-500 text-white' }}">
                                                        {{ $plugin->enabled ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.plugins.destroy', $plugin) }}" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="px-4 py-1 rounded text-sm font-medium bg-red-600 text-white hover:underline ">Delete</button>
                                                </form>
                                            </div>

                                        </div>
                @endforeach
            </div>
@endsection