@php
    $meta = $model->seoMeta->meta ?? [];

    // Title: meta.title or fallback to model title or app name
    $title = $meta['title'] ?? ($model->title ?? config('app.name'));

    // Map your “friendly” robots labels to actual directives
    $robotMap = [
        'Index & Follow' => 'index, follow',
        'NoIndex & Follow' => 'noindex, follow',
        'NoIndex & NoFollow' => 'noindex, nofollow',
        'No Archive' => 'noarchive',
        'No Snippet' => 'nosnippet',
    ];
    $robots = $robotMap[$meta['robots'] ?? 'Index & Follow'];
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $meta['description'] ?? '' }}">
<meta name="keywords" content="{{ $meta['keywords'] ?? '' }}">
<meta name="robots" content="{{ $robots }}">
