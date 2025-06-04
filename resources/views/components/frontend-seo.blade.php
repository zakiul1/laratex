@php
    use Illuminate\Support\Str;

    // Grab the JSON‐decoded array from Product::getSeoAttribute()
    $meta = $model->seo;

    // 1) <title>
    if (!empty($meta['title'])) {
        $seoTitle = $meta['title'];
    } elseif (!empty($model->title ?? $model->name)) {
        $seoTitle = $model->title ?? $model->name;
    } else {
        $seoTitle = config('app.name', 'Laravel');
    }

    // 2) <meta name="description">
    if (!empty($meta['description'])) {
        $seoDescription = $meta['description'];
    } elseif (!empty($model->excerpt)) {
        $seoDescription = Str::limit(strip_tags($model->excerpt), 155, '…');
    } else {
        $raw = $model->description ?? ($model->content ?? '');
        $seoDescription = Str::limit(strip_tags($raw), 155, '…');
        // (optional) fallback to name if that’s still empty:
        if (empty($seoDescription)) {
            $seoDescription = $model->name ?? config('app.name', 'Laravel');
        }
    }

    // 3) Keywords & robots
    $seoKeywords = $meta['keywords'] ?? '';
    $robotMap = [
        'Index & Follow' => 'index, follow',
        'NoIndex & Follow' => 'noindex, follow',
        'NoIndex & NoFollow' => 'noindex, nofollow',
        'No Archive' => 'noarchive',
        'No Snippet' => 'nosnippet',
    ];
    $seoRobots = $robotMap[$meta['robots'] ?? 'Index & Follow'];
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="robots" content="{{ $seoRobots }}">
