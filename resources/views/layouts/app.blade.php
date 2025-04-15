<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Buy finest quality sports, tactical, and workwear gear at WORKYIND">
    <meta name="keywords" content="workwear, tactical gears, sports goods, military uniform, industrial apparel">
    <meta name="author" content="WORKYIND">

    <!-- Open Graph / SEO Tags -->
    <meta property="og:title" content="WORKYIND - Tactical & Workwear Gear">
    <meta property="og:description" content="Top quality tactical, workwear, and sports goods delivered globally">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">

    <title>{{ config('app.name', 'WORKYIND') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-inter bg-white text-gray-900 dark:bg-neutral-950 dark:text-white antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        @include('components.header')

        <!-- Page Content -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('components.footer')
    </div>
    @stack('scripts')
</body>

</html>