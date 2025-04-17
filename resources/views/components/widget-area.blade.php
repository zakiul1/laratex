@foreach ($widgets as $widget)
    <div class="mb-6">
        {{-- Optional Widget Title --}}
        @if ($widget->title)
            <h4 class="text-sm font-bold mb-2">{{ $widget->title }}</h4>
        @endif

        {{-- Render widget based on type --}}
        @switch($widget->widget_type)

            {{-- Text Widget --}}
            @case('text')
                {!! $widget->content !!}
                @break

            {{-- Blade View Widget --}}
            @case('view')
                @includeIf($widget->content)
                @break

            {{-- Menu Widget --}}
            @case('menu')
                @php
                    $menu = \App\Models\Menu::where('slug', $widget->content)->with('items')->first();
                @endphp

                @if ($menu && $menu->items->count())
                    <ul class="space-y-2">
                        @foreach ($menu->items as $item)
                            <li>
                                <a href="{{ $item->url }}" class="text-sm hover:underline">
                                    {{ $item->label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-gray-500">Menu not found or has no items.</p>
                @endif
                @break

            {{-- Future types (e.g. newsletter) --}}
            @default
                <p class="text-sm text-red-500">Unknown widget type: {{ $widget->widget_type }}</p>
        @endswitch
    </div>
@endforeach
