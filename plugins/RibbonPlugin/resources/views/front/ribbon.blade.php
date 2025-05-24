@php
    use Plugins\RibbonPlugin\Models\HeaderRibbon;
    $ribbon = HeaderRibbon::first();
@endphp

@if ($ribbon && $ribbon->is_active)
    <div class="w-full flex justify-center items-center"
        style="
        --ribbon-bg: {{ $ribbon->bg_color }};
        --ribbon-text: {{ $ribbon->text_color }};
        background-color: var(--ribbon-bg);
        color: var(--ribbon-text);
        height: {{ $ribbon->height }}px;
      ">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex space-x-4">
                @if ($ribbon->left_text)
                    <span>{{ $ribbon->left_text }}</span>
                @endif

                @if ($ribbon->center_text)
                    <span>{{ $ribbon->center_text }}</span>
                @endif
            </div>

            <div class="flex items-center space-x-6">
                @if ($ribbon->phone)
                    <a href="tel:{{ $ribbon->phone }}" class="flex items-center space-x-1"
                        style="color: var(--ribbon-text);">
                        {{-- icons use currentColor by default --}}
                        <x-lucide-phone class="w-4 h-4 text-current" />
                        <span class="text-sm leading-none">{{ $ribbon->phone }}</span>
                    </a>
                @endif

                @if ($ribbon->email)
                    <a href="mailto:{{ $ribbon->email }}" class="flex items-center space-x-1"
                        style="color: var(--ribbon-text);">
                        <x-lucide-mail class="w-4 h-4 text-current" />
                        <span class="text-sm leading-none">{{ $ribbon->email }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
