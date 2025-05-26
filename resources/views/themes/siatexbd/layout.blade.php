<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ─── SEO META TAGS ────────────────────────────────────────── --}}
    @if (isset($page))
        @include('components.frontend-seo', ['model' => $page])
    @elseif (isset($post))
        @include('components.frontend-seo', ['model' => $post])
    @elseif (isset($category))
        @include('components.frontend-seo', ['model' => $category])
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
    @endif

    {{-- ─── STYLES & SCRIPTS ──────────────────────────────────────── --}}
    <link rel="stylesheet" href="{{ asset('blockeditor/layout-frontend.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        // pull any saved custom container width (in px), default to 1170
        $opts = $themeSettings->options ?? [];
        $containerWidth = data_get($opts, 'container_width', 1170);
    @endphp

    {{-- ─── INITIAL CUSTOM CSS from DB ───────────────────────────── --}}
    @if ($themeSettings->custom_css)
        <style data-live-preview id="initial-custom-css">
            {!! $themeSettings->custom_css !!}
        </style>
    @endif

    {{-- ─── INITIAL CSS VARS & TYPOGRAPHY & CONTAINER ─────────────── --}}
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
            /* ← fallback changed from 1100 to 1170px */
            --container-width: {{ $containerWidth }}px;
        }

        /* Utility container */
        .container {
            max-width: var(--container-width, 1170px);
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

        /* ul, ol { list-style-type: var(--list-marker); } */
        /* a { color: var(--anchor-color); } */
    </style>

    {{-- ─── LIVE-PREVIEW LISTENER ─────────────────────────────────── --}}
    <script>
        window.addEventListener('message', e => {
            const msg = e.data;
            if (!msg || msg.type !== 'themePreview') return;

            // update CSS variables
            document.documentElement.style.setProperty('--primary-color', msg.primaryColor);
            document.documentElement.style.setProperty('--link-color', msg.linkColor);
            document.documentElement.style.setProperty('--body-font', msg.fontFamily);
            document.documentElement.style.setProperty('--container-width', msg.containerWidth + 'px');

            if (msg.typography) {
                const t = msg.typography;
                document.documentElement.style.setProperty('--h1-size', t.headings.h1 + 'px');
                document.documentElement.style.setProperty('--h2-size', t.headings.h2 + 'px');
                document.documentElement.style.setProperty('--h3-size', t.headings.h3 + 'px');
                document.documentElement.style.setProperty('--h4-size', t.headings.h4 + 'px');
                document.documentElement.style.setProperty('--h5-size', t.headings.h5 + 'px');
                document.documentElement.style.setProperty('--h6-size', t.headings.h6 + 'px');
                document.documentElement.style.setProperty('--strong-weight', t.strong.weight);
                document.documentElement.style.setProperty('--para-line-height', t.paragraph.line_height);
                document.documentElement.style.setProperty('--list-marker', t.list.marker);
                document.documentElement.style.setProperty('--anchor-color', t.anchor.color);
            }

            // custom CSS override
            let custom = document.getElementById('live-custom-css');
            if (!custom) {
                custom = document.createElement('style');
                custom.id = 'live-custom-css';
                document.head.appendChild(custom);
            }
            custom.textContent = msg.customCss;

            // header/title updates
            const titleEl = document.querySelector('.site-title');
            if (titleEl) {
                titleEl.style.color = msg.siteTitleColor;
                titleEl.textContent = msg.siteTitle;
            }
            const tagEl = document.querySelector('.site-tagline');
            if (tagEl) {
                tagEl.style.display = msg.showTagline ? '' : 'none';
                tagEl.style.color = msg.taglineColor;
                tagEl.textContent = msg.tagline;
            }
            const phoneEl = document.querySelector('.contact-phone');
            if (phoneEl) phoneEl.textContent = msg.contactPhone;

            // footer text
            const foot = document.querySelector('footer');
            if (foot) foot.textContent = msg.footerText;
        });
    </script>
</head>

<body class="text-gray-900 dark:bg-neutral-950 dark:text-white">
    <div class="min-h-screen flex flex-col">
        {{-- Site Header --}}
        @include('partials.header')

        {{-- Page Content --}}
        <main class="flex-grow">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('partials.footer')
    </div>
    @include('partials.dynamic-cart')
    @stack('scripts')
</body>

</html>
