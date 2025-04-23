@php
    use Plugins\RibbonPlugin\Models\HeaderRibbon;
    $ribbon = HeaderRibbon::first();
@endphp

@if ($ribbon && $ribbon->is_active)
    <div style="background: {{ $ribbon->bg_color }}; color: {{ $ribbon->text_color }}; height: {{ $ribbon->height }}px;"
        class="w-full">
        <div
            class="container mx-auto  sm:px-6 md:px-4 lg:px-4
                        flex flex-col sm:flex-row items-center justify-between
                        py-2">
            {{-- Left & Center Text --}}
            <div
                class="flex flex-col sm:flex-row items-center
                            space-y-1 sm:space-y-0 sm:space-x-4
                            text-xs sm:text-sm text-center sm:text-left">
                @if ($ribbon->left_text)
                    <span class="block">{{ $ribbon->left_text }}</span>
                @endif

                @if ($ribbon->center_text)
                    <span class="block">{{ $ribbon->center_text }}</span>
                @endif
            </div>

            {{-- Contact Links --}}
            <div
                class="flex flex-col sm:flex-row items-center
                            space-y-1 sm:space-y-0 sm:space-x-6
                            mt-2 sm:mt-0 text-xs sm:text-sm">
                @if ($ribbon->phone)
                    <a href="tel:{{ $ribbon->phone }}"
                        class="flex items-center justify-center sm:justify-start space-x-1">
                        <x-lucide-phone class="w-4 h-4" />
                        <span class="leading-none">{{ $ribbon->phone }}</span>
                    </a>
                @endif

                @if ($ribbon->email)
                    <a href="mailto:{{ $ribbon->email }}"
                        class="flex items-center justify-center sm:justify-start space-x-1">
                        <x-lucide-mail class="w-4 h-4" />
                        <span class="leading-none">{{ $ribbon->email }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
