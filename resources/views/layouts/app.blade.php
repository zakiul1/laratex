<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('blockeditor/layout-frontend.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-neutral-950 dark:text-white">

    <div class="min-h-screen flex flex-col">

        <!-- Global Header Component -->
        <x-header />

        <!-- Page Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Global Footer Component -->
        <x-footer />

    </div>

</body>

</html>
