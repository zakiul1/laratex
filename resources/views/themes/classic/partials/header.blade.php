{{-- resources/views/partials/header.blade.php --}}
<header x-data="{ mobileOpen: false, activeIndex: null }" class="bg-white shadow">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      {{-- Logo or Theme Name --}}
      @php $setting = \App\Models\ThemeSetting::first(); @endphp
      <a href="{{ route('home') }}" class="flex items-center">
        @if($setting?->logo)
          <img src="{{ asset('storage/'.$setting->logo) }}" alt="Logo" class="h-8">
        @else
          <span class="text-2xl font-bold">
            {{ $setting?->theme ?? config('app.name') }}
          </span>
        @endif
      </a>
  
      {{-- Desktop Navigation --}}
      @php $menuItems = apply_filters('front_header_menu', collect()); @endphp
      <nav class="hidden lg:flex space-x-8">
        @foreach($menuItems as $i => $item)
          @php $hasChildren = $item->children->isNotEmpty(); @endphp
  
          <div class="relative"
               x-on:mouseenter="activeIndex = {{ $i }}"
               x-on:mouseleave="activeIndex = null">
            <a href="{{ $item->url }}"
               class="flex items-center text-gray-700 hover:text-gray-900">
              <span>{{ $item->title }}</span>
              @if($hasChildren)
                <x-lucide-chevron-down class="w-4 h-4 ml-1"/>
              @endif
            </a>
  
            @if($hasChildren)
              <div
                x-show="activeIndex === {{ $i }}"
                x-cloak
                x-on:mouseenter="activeIndex = {{ $i }}"
                x-on:mouseleave="activeIndex = null"
                class="absolute top-full left-0 w-screen max-w-md bg-white shadow-lg border border-gray-200 p-6 grid grid-cols-2 gap-6 z-50"
              >
                @foreach($item->children as $child)
                  <div>
                    <a href="{{ $child->url }}"
                       class="block font-semibold mb-2 hover:text-primary">
                      {{ $child->title }}
                    </a>
                    @if($child->children->isNotEmpty())
                      <ul class="space-y-1">
                        @foreach($child->children as $grand)
                          <li>
                            <a href="{{ $grand->url }}"
                               class="block text-gray-600 hover:text-primary">
                              {{ $grand->title }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </nav>
  
      {{-- Search, Inquiry & Mobile Toggle --}}
      <div class="flex items-center space-x-4">
        <button class="text-gray-700 hover:text-gray-900">
          <x-lucide-search class="w-5 h-5"/>
        </button>
        <a href="   "
           class="px-4 py-2 border border-primary text-primary rounded hover:bg-primary hover:text-white transition">
          Inquiry
        </a>
        <button @click="mobileOpen = !mobileOpen" class="lg:hidden focus:outline-none">
          <x-lucide-menu class="w-6 h-6"/>
        </button>
      </div>
    </div>
  
    {{-- Mobile Menu --}}
    <div x-show="mobileOpen" x-cloak class="lg:hidden bg-white border-t border-gray-200">
      <div class="px-4 py-5">
        <ul class="space-y-4">
          @foreach($menuItems as $item)
            <li>
              <a href="{{ $item->url }}" class="block text-gray-700 font-medium">
                {{ $item->title }}
              </a>
              @if($item->children->isNotEmpty())
                <ul class="mt-2 pl-4 space-y-2 border-l border-gray-200">
                  @foreach($item->children as $child)
                    <li>
                      <a href="{{ $child->url }}" class="block text-gray-600">
                        {{ $child->title }}
                      </a>
                    </li>
                  @endforeach
                </ul>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </header>
  