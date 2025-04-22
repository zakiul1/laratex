@php
    use Plugins\RibbonPlugin\Models\HeaderRibbon;
    // 1) Fetch once
    $ribbon = HeaderRibbon::first();
@endphp

{{-- 2) Only render if we actually have a record and it’s active --}}
@if ($ribbon !== null && $ribbon->is_active)
    <div style="background: {{ $ribbon->bg_color }};
               color:      {{ $ribbon->text_color }};
               height:     {{ $ribbon->height }}px;"
        class="w-full  flex justify-center items-center">
        <div class="container mx-auto px-4  flex items-center justify-between">
            <div class="flex space-x-4 ">
                @if ($ribbon->left_text)
                    <span>{{ $ribbon->left_text }}</span>
                @endif

                @if ($ribbon->center_text)
                    <span>{{ $ribbon->center_text }}</span>
                @endif
            </div>

            <div class="flex items-center space-x-6">
                @if ($ribbon->phone)
                    <a href="tel:{{ $ribbon->phone }}" class="flex items-center space-x-1">
                        {{-- use the blade‑icon component instead of inline SVG --}}
                        <x-lucide-phone class="w-4 h-4" />
                        <span class="text-sm leading-none">{{ $ribbon->phone }}</span>
                    </a>
                @endif

                @if ($ribbon->email)
                    <a href="mailto:{{ $ribbon->email }}" class="flex  items-center space-x-1">
                        <x-lucide-mail class="w-4 h-4" />
                        <span class="text-sm leading-none mb-1">{{ $ribbon->email }}</span>
                    </a>
                @endif
            </div>

        </div>

    </div>
@endif
