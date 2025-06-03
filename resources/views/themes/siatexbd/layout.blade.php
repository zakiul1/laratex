<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- ─── SEO META TAGS ─────────────────────────────────────── --}}
    @if (isset($page))
        @include('components.frontend-seo', ['model' => $page])
    @elseif (isset($post))
        @include('components.frontend-seo', ['model' => $post])
    @elseif (isset($category))
        @include('components.frontend-seo', ['model' => $category])
    @elseif (isset($product))
        @include('components.frontend-seo', ['model' => $product])
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
    @endif

    {{-- ─── HIDE x-cloak UNTIL ALPINE INIT ─────────────────────── --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- ─── LOCAL @font-face DEFINITION FOR ROPA SANS ─────────────────── --}}
    <style>
        /* Regular */
        @font-face {
            font-family: 'Ropa Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/ropasans/RopaSans-Regular.ttf') }}') format('truetype');

        }

        /* Italic */
        @font-face {
            font-family: 'Ropa Sans';
            font-style: italic;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/ropasans/RopaSans-Italic.ttf') }}') format('truetype');
            /* Similarly, add WOFF2 if available:
               src: url('{{ asset('fonts/ropasans/RopaSans-Italic.woff2') }}') format('woff2'),
                    url('{{ asset('fonts/ropasans/RopaSans-Italic.ttf') }}') format('truetype');
            */
        }

        /* Utility class so you can easily apply “font-ropa-sans” in your components */
        .font-ropa-sans {
            font-family: 'Ropa Sans', sans-serif;
        }
    </style>

    {{-- ─── BLOCKEDITOR CSS (PRELOAD + APPLY) ─────────────────────── --}}
    <link rel="preload" as="style" href="{{ asset('blockeditor/layout-frontend.css') }}"
        onload="this.rel='stylesheet'" />
    <noscript>
        <link rel="stylesheet" href="{{ asset('blockeditor/layout-frontend.css') }}" />
    </noscript>

    {{-- ─── VITE ASSETS ───────────────────────────────────────────── --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $opts = $themeSettings->options ?? [];
        $containerWidth = data_get($opts, 'container_width', 1200);
    @endphp

    {{-- ─── INITIAL CUSTOM CSS ────────────────────────────────────── --}}
    @if ($themeSettings->custom_css)
        <style data-live-preview id="initial-custom-css">
            {!! $themeSettings->custom_css !!}
        </style>
    @endif

    {{-- ─── CSS VARS & TYPOGRAPHY & CONTAINER ─────────────────────── --}}
    <style data-live-preview id="initial-vars">
        :root {
            --primary-color: {{ $themeSettings->primary_color }};
            --link-color: {{ data_get($opts, 'typography.anchor.color', $themeSettings->primary_color) }};
            --body-font: {{ $themeSettings->font_family }};
            --h1-size: {{ data_get($opts, 'typography.headings.h1', 32) }}px;
            --h2-size: {{ data_get($opts, 'typography.headings.h2', 28) }}px;
            --h3-size: {{ data_get($opts, 'typography.headings.h3', 24) }}px;
            --h4-size: {{ data_get($opts, 'typography.headings.h4', 20) }}px;
            --h5-size: {{ data_get($opts, 'typography.headings.h5', 16) }}px;
            --h6-size: {{ data_get($opts, 'typography.headings.h6', 14) }}px;
            --strong-weight: {{ data_get($opts, 'typography.strong.weight', 700) }};
            --para-line-height: {{ data_get($opts, 'typography.paragraph.line_height', 1.6) }};
            --list-marker: {{ data_get($opts, 'typography.list.marker', 'disc') }};
            --anchor-color: {{ data_get($opts, 'typography.anchor.color', $themeSettings->primary_color) }};
            --container-width: {{ $containerWidth }}px;
        }

        .container {
            max-width: var(--container-width, 1200px);
            margin-left: auto;
            margin-right: auto;
        }

        body {
            font-family: var(--body-font);
        }

        h1 {
            font-size: var(--h1-size);
        }

        h2 {
            font-size: var(--h2-size);
        }

        h3 {
            font-size: var(--h3-size);
        }

        h4 {
            font-size: var(--h4-size);
        }

        h5 {
            font-size: var(--h5-size);
        }

        h6 {
            font-size: var(--h6-size);
        }

        strong {
            font-weight: var(--strong-weight);
        }

        p {
            line-height: var(--para-line-height);
        }
    </style>

    {{-- ─── LIVE PREVIEW LISTENER ─────────────────────────────────── --}}
    <script>
        window.addEventListener('message', e => {
            const msg = e.data;
            if (!msg || msg.type !== 'themePreview') return;
            // …apply CSS vars, custom CSS, header/footer updates…
        });
    </script>

    {{-- ─── CHILD VIEWS CAN STILL PUSH INTO HEAD ───────────────────── --}}
    @stack('head')
</head>

<body class="text-gray-900 dark:bg-neutral-950 dark:text-white">
    <div class="min-h-screen flex flex-col">
        {{-- Site Header --}}
        @include('partials.header')

        {{-- Main Content --}}
        <main class="flex-grow">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('partials.footer')
    </div>
    @include('partials.dynamic-cart')
    {{-- Any scripts pushed by child views --}}
    @stack('scripts')
</body>

</html>
