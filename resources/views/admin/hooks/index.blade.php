@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-bold mb-6">Registered Hooks by Plugin</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h2 class="text-lg font-semibold mb-2">Actions</h2>
            @foreach ($actions as $plugin => $hooks)
                <div class="mb-4">
                    <h3 class="font-bold">{{ $plugin }}</h3>
                    <ul class="text-sm ml-4 list-disc">
                        @foreach ($hooks as $hook)
                            <li>
                                <strong>{{ $hook['hook'] }}</strong>
                                @if($hook['callback'] !== 'Closure')
                                    → {{ $hook['callback'] }}
                                @endif
                                <span class="text-gray-500"> (priority: {{ $hook['priority'] }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-2">Filters</h2>
            @foreach ($filters as $plugin => $hooks)
                <div class="mb-4">
                    <h3 class="font-bold">{{ $plugin }}</h3>
                    <ul class="text-sm ml-4 list-disc">
                        @foreach ($hooks as $hook)
                            <li>
                                <strong>{{ $hook['hook'] }}</strong>
                                @if($hook['callback'] !== 'Closure')
                                    → {{ $hook['callback'] }}
                                @endif
                                <span class="text-gray-500"> (priority: {{ $hook['priority'] }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection