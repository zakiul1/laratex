<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Inject Theme Customization --}}
    @php
        $themeSettings = \App\Models\ThemeSetting::where('theme', getActiveTheme())->first();
    @endphp

    @if ($themeSettings && $themeSettings->custom_css)
        <style>{!! $themeSettings->custom_css !!}</style>
    @endif

    @if ($themeSettings && $themeSettings->primary_color)
        <style>
            :root {
                --primary-color: {{ $themeSettings->primary_color }};
            }
        </style>
    @endif

    @if ($themeSettings && $themeSettings->font_family)
        <style>
            body {
                font-family: {{ $themeSettings->font_family }};
            }
        </style>
    @endif
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-neutral-950 dark:text-white">

    <div class="min-h-screen flex flex-col">

        <!-- Global Header Component -->
        @include('includes.header')

        <!-- Page Content -->
        <main class="flex-grow">
            @php
                $sections = \App\Models\ThemeSection::where('theme', getActiveTheme())->orderBy('order')->get();
            @endphp

            @foreach ($sections as $section)
                @includeIf('themes.' . getActiveTheme() . '.sections.' . $section->key)
            @endforeach
        </main>

        <!-- Global Footer Component -->
        @include('includes.footer')

    </div>

    @stack('scripts')
</body>

</html>
