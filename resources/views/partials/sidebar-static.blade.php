@php
    $route = fn($name) => request()->routeIs($name)
        ? 'bg-slate-300 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong'
        : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:bg-primary-dark/5 dark:hover:text-on-surface-dark-strong';
@endphp

<a href="{{ route('dashboard') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('dashboard') }}">
    <x-lucide-home class="size-6" />
    <span>Dashboard</span>
</a>

<a href="{{ route('posts.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('posts.*') }}">
    <x-lucide-pencil class="size-6" />
    <span>Posts</span>
</a>

<a href="{{ route('pages.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('pages.*') }}">
    <x-lucide-file-text class="size-6" />
    <span>Pages</span>
</a>

<a href="{{ route('menus.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('menus.*') }}">
    <x-lucide-list class="size-6" />
    <span>Menus</span>
</a>

<a href="{{ route('categories.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('categories.*') }}">
    <x-lucide-folder class="size-6" />
    <span>Categories</span>
</a>

<a href="{{ route('products.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('products.*') }}">
    <x-lucide-package class="size-6" />
    <span>Products</span>
</a>

{{-- <a href="{{ route('sliders.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('sliders.*') }}">
    <x-lucide-images class="size-6" />
    <span>Sliders</span>
</a> --}}

<a href="{{ route('admin.contact.edit') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('admin.contact.*') }}">
    <x-lucide-mail class="size-6" />
    <span>Contact Page</span>
</a>

{{-- <a href="{{ route('site-settings.edit') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('site-settings.*') }}">
    <x-lucide-settings class="size-6" />
    <span>Site Settings</span>
</a> --}}
<a href="{{ route('media.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ request()->routeIs('media.*') ? 'bg-gray-200' : '' }}">
    <x-lucide-image class="size-6" />
    <span>Media Library</span>
</a>
<a href="{{ route('admin.plugins.index') }}"
    class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium {{ $route('admin.plugins.*') }}">
    <x-lucide-plug class="size-6" />
    <span>Plugin Manager</span>
</a>

{{-- Theme nested menu --}}
@php $isThemeActive = request()->routeIs('themes.*') || request()->routeIs('widgets.*'); @endphp

<div x-data='@json(['isExpanded' => (bool) $isThemeActive])' class="flex flex-col">
    <button type="button" @click="isExpanded = !isExpanded" id="theme-btn" aria-controls="theme-menu"
        x-bind:aria-expanded="isExpanded ? 'true' : 'false'"
        class="flex items-center rounded-radius gap-2 px-2 py-1.5 text-sm font-medium 
        {{ $isThemeActive ? 'bg-slate-300 text-on-surface-strong dark:bg-primary-dark/10 dark:text-on-surface-dark-strong' : 'text-on-surface hover:bg-primary/5 hover:text-on-surface-strong dark:text-on-surface-dark dark:hover:text-on-surface-dark-strong dark:hover:bg-primary-dark/5' }}">

        <x-lucide-layout class="size-5 shrink-0" />
        <span class="mr-auto text-left">Theme</span>

        <!-- Visual indicator arrow for nested items -->
        <x-lucide-chevron-down class="size-4 transition-transform shrink-0 ml-auto"
            x-bind:class="isExpanded ? 'rotate-180' : 'rotate-0'" />
    </button>

    <ul class="ml-6" x-cloak x-show="isExpanded" x-collapse aria-labelledby="theme-btn" id="theme-menu">
        <li class="px-1 py-0.5 first:mt-2">
            <a href="{{ route('themes.index') }}"
                class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-radius {{ $route('themes.index') }}">
                All Themes
            </a>
        </li>
        <li class="px-1 py-0.5">
            <a href="{{ route('themes.customize') }}"
                class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-radius {{ $route('themes.customize') }}">
                Customize Theme
            </a>
        </li>
        <li class="px-1 py-0.5">
            <a href="{{ route('widgets.index') }}"
                class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-radius {{ $route('widgets.*') }}">
                Widgets
            </a>
        </li>
    </ul>
</div>
