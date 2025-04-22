<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    {{-- Tailwind from CDN for quick styling --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 text-lg font-semibold">
            {{ config('app.name', 'My Site') }}
        </div>
    </header>

    <main class="py-12">
        <div class="max-w-7xl mx-auto px-4">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 py-4 text-sm text-gray-500 text-center">
            &copy; {{ date('Y') }} {{ config('app.name', 'My Site') }}. All rights reserved.
        </div>
    </footer>
</body>

</html>
