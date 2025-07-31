{{-- resources/views/partials/front_header.blade.php --}}
@php
    $opts = $themeSettings->options ?? [];
    $logo = $themeSettings->logo;
    $siteTitle = data_get($opts, 'site_title', config('app.name'));
    $titleColor = data_get($opts, 'site_title_color', '#000');
    $tagline = data_get($opts, 'tagline', '');
    $email = data_get($opts, 'contact_email', null);
    $phone = data_get($opts, 'contact_phone', null);
    $menuItems = apply_filters('front_header_menu', collect());
@endphp

<header x-data="{ mobileOpen: false }" class="bg-white border-b">
    {{-- Ribbon (desktop only) --}}
    <div class="hidden md:block">
        {!! apply_filters('frontend_ribbon', '') !!}
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <div class="w-32 h-10 md:w-40 md:h-12 flex items-center overflow-hidden">
                    @if ($logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="w-full h-full object-contain"
                            loading="eager" fetchpriority="high" />
                    @else
                        <span class="text-xl md:text-2xl font-bold flex">
                            <span class="text-blue-600">SIATEX</span>
                            <span class="text-red-600 ml-1">Global</span>
                        </span>
                    @endif
                </div>
            </a>

            {{-- Desktop nav + contact --}}
            <div class="hidden lg:flex items-center space-x-8">
                <nav class="flex space-x-8">
                    @foreach ($menuItems as $item)
                        <div class="relative group">
                            <a href="{{ $item->url }}" class="text-black hover:text-blue-600 transition">
                                {{ $item->title }}
                            </a>
                            @if ($item->children->isNotEmpty())
                                <ul
                                    class="absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg
                         opacity-0 group-hover:opacity-100 transition
                         pointer-events-none group-hover:pointer-events-auto">
                                    @foreach ($item->children as $child)
                                        <li>
                                            <a href="{{ $child->url }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                                {{ $child->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </nav>

                <div class="flex items-center space-x-6 text-sm text-gray-600">
                    @if ($phone)
                        <a href="tel:{{ $phone }}" class="flex items-center hover:text-blue-600">
                            <x-lucide-phone class="w-5 h-5 mr-1" /> {{ $phone }}
                        </a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="flex items-center hover:text-blue-600">
                            <x-lucide-mail class="w-5 h-5 mr-1" /> {{ $email }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Mobile toggle --}}
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-700 hover:text-gray-900"
                :aria-expanded="mobileOpen" aria-label="Toggle navigation menu">
                <x-lucide-menu class="w-6 h-6" />
            </button>

        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-cloak class="lg:hidden bg-white border-t border-gray-200">
        <div class="px-4 py-5 space-y-4">
            @foreach ($menuItems as $item)
                <div>
                    <a href="{{ $item->url }}" class="block font-semibold text-gray-800">
                        {{ $item->title }}
                    </a>
                    @if ($item->children->isNotEmpty())
                        <ul class="mt-2 pl-4 space-y-2 border-l border-gray-200">
                            @foreach ($item->children as $child)
                                <li>
                                    <a href="{{ $child->url }}" class="block text-gray-600">
                                        {{ $child->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <div class="pt-4 border-t border-gray-200 space-y-2">
                @if ($phone)
                    <a href="tel:{{ $phone }}" class="flex items-center text-gray-700">
                        <x-lucide-phone class="w-5 h-5 mr-2" /> {{ $phone }}
                    </a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="flex items-center text-gray-700">
                        <x-lucide-mail class="w-5 h-5 mr-2" /> {{ $email }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
