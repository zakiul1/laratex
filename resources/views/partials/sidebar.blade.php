<nav x-cloak
    class="fixed left-0 z-30 flex h-svh w-60 shrink-0 flex-col border-r border-outline bg-surface-alt p-4 transition-transform duration-300 md:w-64 md:translate-x-0 md:relative dark:border-outline-dark dark:bg-surface-dark-alt"
    x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'" aria-label="sidebar navigation">

    <!-- logo  -->
    <a href="{{ route('dashboard') }}"
        class="ml-2 w-fit text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
        Siatex
    </a>

    <!-- search  -->
    <div class="relative my-4 flex w-full max-w-xs flex-col gap-1 text-on-surface dark:text-on-surface-dark">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2"
            class="absolute left-2 top-1/2 size-5 -translate-y-1/2 text-on-surface/50 dark:text-on-surface-dark/50"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="search"
            class="w-full border border-outline rounded-radius bg-surface px-2 py-1.5 pl-9 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-75 dark:border-outline-dark dark:bg-surface-dark/50 dark:focus-visible:outline-primary-dark"
            name="search" aria-label="Search" placeholder="Search" />
    </div>

    <!-- sidebar links  -->
    <div class="flex flex-col gap-2 overflow-y-auto pb-6">
        {{-- Static items --}}
        @include('partials.sidebar-static')
        {{-- resources/views/partials/admin_sidebar_plugins.blade.php --}}
        @php
            $pluginMenus = apply_filters('admin_sidebar_menu', []);
        @endphp

        @foreach ($pluginMenus as $menu)
            @if (empty($menu['children']))
                {{-- Single link (no children) --}}
                @php
                    $isActive = Route::currentRouteName() === $menu['route'];
                    //dd($isActive);
                @endphp

                <a href="{{ route($menu['route']) }}"
                    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium text-on-surface hover:bg-primary/5 dark:text-on-surface-dark dark:hover:bg-primary-dark/5
        {{ $isActive ? 'bg-white/10 text-blue-800 ring-1 ring-white/20 shadow-md dark:text-primary-dark' : '' }}">

                    @if (!empty($menu['icon']))
                        <x-dynamic-component :component="$menu['icon']" class="size-5 shrink-0" />
                    @endif
                    <span>{{ $menu['label'] }}</span>
                </a>
            @else
                {{-- Collapsible parent with children --}}
                <div x-data="{ open: true }" class="flex flex-col">
                    <button @click="open = !open"
                        class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium text-on-surface hover:bg-primary/5 dark:text-on-surface-dark dark:hover:bg-primary-dark/5">
                        @if (!empty($menu['icon']))
                            <x-dynamic-component :component="$menu['icon']" class="size-5 shrink-0" />
                        @endif
                        <span class="mr-auto">{{ $menu['label'] }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-transform shrink-0"
                            fill="currentColor" viewBox="0 0 20 20" :class="open ? 'rotate-180' : 'rotate-0'">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.72-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.01-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <ul x-show="open" x-transition class="ml-6 mt-1 text-sm space-y-1">
                        @foreach ($menu['children'] as $child)
                            <li>
                                <a href="{{ route($child['route']) }}"
                                    class="block px-2 py-1 rounded hover:bg-primary/5 dark:hover:bg-primary-dark/5">
                                    {{ $child['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach


        <a href="{{ route('site-settings.edit') }}"
            class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2 focus-visible:underline focus:outline-hidden
                {{ request()->routeIs('site-settings.*')
                    ? 'bg-slate-300 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong'
                    : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong' }}">

            <!-- Site Settings Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>

            <span>Site Settings</span>
        </a>
    </div>
</nav>
