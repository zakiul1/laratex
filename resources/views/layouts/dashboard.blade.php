<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
   

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="antialiased bg-gray-100 text-gray-900 dark:bg-neutral-950 dark:text-white">

    <div x-data="{ sidebarIsOpen: false }" class="relative flex w-full flex-col md:flex-row">
        <!-- Skip Link -->
        <a class="sr-only" href="#main-content">skip to the main content</a>

        <!-- Overlay -->
        <div x-cloak x-show="sidebarIsOpen" class="fixed inset-0 z-20 bg-black/30 backdrop-blur-sm md:hidden"
            aria-hidden="true" @click="sidebarIsOpen = false" x-transition.opacity></div>

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content with Top Navbar -->
        <div class="h-svh w-full overflow-y-auto bg-white dark:bg-neutral-950">
            <!-- Top Navbar -->
        <nav class="sticky top-0 z-10 flex items-center justify-between border-b border-outline bg-surface-alt px-4 py-2 dark:border-outline-dark dark:bg-surface-dark-alt"
            aria-label="top navibation bar">
        
            <!-- sidebar toggle button for small screens  -->
            <button type="button" class="md:hidden inline-block text-on-surface dark:text-on-surface-dark"
                x-on:click="sidebarIsOpen = true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"
                    aria-hidden="true">
                    <path
                        d="M0 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5-1v12h9a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zM4 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2z" />
                </svg>
                <span class="sr-only">sidebar toggle</span>
            </button>
        
            <!-- breadcrumbs  -->
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-1 text-sm font-medium text-on-surface hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:text-on-surface-dark-strong border border-outline dark:border-outline-dark px-3 py-1.5 rounded-radius bg-white dark:bg-surface-dark shadow-sm hover:bg-primary/5 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back
        </a>

        
            <!-- Profile Menu  -->
        <div x-data="{ userDropdownIsOpen: false }" class="relative" x-on:keydown.esc.window="userDropdownIsOpen = false">
            <button type="button"
                class="flex w-full items-center rounded-radius gap-2 p-2 text-left text-on-surface hover:bg-primary/5 hover:text-on-surface-strong focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong dark:focus-visible:outline-primary-dark"
                x-bind:class="userDropdownIsOpen ? 'bg-primary/10 dark:bg-primary-dark/10' : ''" aria-haspopup="true"
                x-on:click="userDropdownIsOpen = ! userDropdownIsOpen" x-bind:aria-expanded="userDropdownIsOpen">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random"
                    class="size-8 object-cover rounded-radius" alt="avatar" aria-hidden="true" />
                <div class="hidden md:flex flex-col">
                    <span class="text-sm font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                        {{ Auth::user()->name }}
                    </span>
                    <span class="text-xs" aria-hidden="true">{{ Auth::user()->email }}</span>
                    <span class="sr-only">profile settings</span>
                </div>
            </button>
        
            <!-- Dropdown menu -->
            <div x-cloak x-show="userDropdownIsOpen"
                class="absolute top-14 right-0 z-20 w-48 border divide-y divide-outline border-outline bg-surface dark:divide-outline-dark dark:border-outline-dark dark:bg-surface-dark rounded-radius"
                role="menu" x-on:click.outside="userDropdownIsOpen = false" x-on:keydown.down.prevent="$focus.wrap().next()"
                x-on:keydown.up.prevent="$focus.wrap().previous()" x-transition x-trap="userDropdownIsOpen">
        
                <!-- Profile link -->
                <div class="flex flex-col py-1.5">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 px-2 py-1.5 text-sm font-medium text-on-surface underline-offset-2 hover:bg-primary/5 hover:text-on-surface-strong focus-visible:underline focus:outline-hidden dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong"
                        role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0">
                            <path
                                d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z" />
                        </svg>
                        <span>Profile</span>
                    </a>
                </div>
        
                <!-- Logout -->
                <div class="flex flex-col py-1.5">
                    <form method="POST" action="{{ route('logout') }}" role="menuitem">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2 px-2 py-1.5 text-sm font-medium text-left text-on-surface underline-offset-2 hover:bg-primary/5 hover:text-on-surface-strong focus-visible:underline focus:outline-hidden dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="size-5 shrink-0"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Zm3 5.75a.75.75 0 0 1 .75-.75h9.546l-1.048-.943a.75.75 0 1 1 1.004-1.114l2.5 2.25a.75.75 0 0 1 0 1.114l-2.5 2.25a.75.75 0 1 1-1.004-1.114l1.048-.943H6.75A.75.75 0 0 1 6 10Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        </nav>

            <!-- Dynamic Page Content -->
            <main id="main-content" class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

</body>

</html>