<nav x-cloak
    class="fixed left-0 z-30 flex h-svh w-60 shrink-0 flex-col border-r border-outline bg-surface-alt p-4 transition-transform duration-300 md:w-64 md:translate-x-0 md:relative dark:border-outline-dark dark:bg-surface-dark-alt"
    x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'" aria-label="sidebar navigation">
    <!-- logo  -->
    <a href="#" class="ml-2 w-fit text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
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
<!-- Dashboard links  -->
    <a href="{{ route('dashboard') }}"
        class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2 focus-visible:underline focus:outline-hidden
        {{ request()->routeIs('dashboard')
    ? 'bg-primary/10 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong'
    : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong' }}">
    
        <!-- icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10.707 1.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 9.414V17a1 1 0 001 1h3a1 1 0 001-1v-4h2v4a1 1 0 001 1h3a1 1 0 001-1V9.414l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>
    
        <span>Dashboard</span>
    </a>

<!-- Posts links  -->
@php
$isActive = request()->routeIs('posts.*');
@endphp

<a href="{{ route('posts.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2
        {{ $isActive ? 'bg-primary/10 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong' : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong' }}">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-5 shrink-0">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M16.5 3.75h2.25A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H16.5m-9 0H4.5A2.25 2.25 0 0 1 2.25 18V6A2.25 2.25 0 0 1 4.5 3.75H7.5m0 0v16m9-16v16" />
    </svg>
    <span>Posts</span>
</a>


<!-- Menu links  -->
@php
$currentRoute = request()->routeIs('menus.*');
@endphp



<!-- Ribbons Settings links  -->
    <a href="{{ route('ribbons.index') }}"
        class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2 focus-visible:underline focus:outline-hidden
        {{ request()->routeIs('ribbons.*')
    ? 'bg-primary/10 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong'
    : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong' }}">
    
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0"
            aria-hidden="true">
            <path fill-rule="evenodd"
                d="M2 3.5A1.5 1.5 0 0 1 3.5 2h13A1.5 1.5 0 0 1 18 3.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 2 16.5v-13ZM5 7a1 1 0 1 1 2 0 1 1 0 0 1-2 0Zm0 3.5a.5.5 0 0 0-.5.5v3h11v-3a.5.5 0 0 0-.5-.5h-10Z"
                clip-rule="evenodd" />
        </svg>
    
        <span>Ribbon Settings</span>
    </a>

    <a href="{{ route('sliders.index') }}"
        class="{{ request()->routeIs('sliders.*') ? 'bg-primary/10 dark:bg-primary-dark/10 text-on-surface-strong dark:text-on-surface-dark-strong' : 'text-on-surface dark:text-on-surface-dark' }} flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium hover:bg-primary/5 hover:text-on-surface-strong dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong">
    
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-5 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M3 12h18M3 19h18" />
        </svg>
    
        <span>Sliders</span>
    </a>

    </div>
</nav>