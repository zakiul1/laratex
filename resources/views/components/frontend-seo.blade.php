{{-- resources/views/components/frontend-seo.blade.php --}}
@php
    use Illuminate\Support\Str;

    // Since Product::getSeoAttribute() already returns an array,
    // just grab that directly. If this is a Page or Category or Post,
    // you might have a similar accessor.
    $meta = $model->seo ?? [];

    //
    // 1) Compute the <title>:
    //
    //    – If the user explicitly set 'title' inside the JSON blob, use it.
    //    – Otherwise, fallback to $model->title or $model->name (if exists) or app name.
    //

    if (!empty($meta['title'])) {
        $seoTitle = $meta['title'];
    } elseif (!empty($model->title)) {
        $seoTitle = $model->title;
    } elseif (!empty($model->name)) {
        $seoTitle = $model->name;
    } else {
        $seoTitle = config('app.name', 'Laravel');
    }

    //
    // 2) Compute the <meta name="description">:
    //
    if (!empty($meta['description'])) {
        $seoDescription = $meta['description'];
    } elseif (!empty($model->excerpt)) {
        $seoDescription = Str::limit(strip_tags($model->excerpt), 155, '…');
    } else {
        // Possible fields: description (for products), content (for posts/pages), etc.
        $raw = $model->description ?? ($model->content ?? '');
        $seoDescription = Str::limit(strip_tags($raw), 155, '…');
    }

    //
    // 3) Keywords tag:
    //
    $seoKeywords = $meta['keywords'] ?? '';

    //
    // 4) Robots directive:
    //
    $robotMap = [
        'Index & Follow' => 'index, follow',
        'NoIndex & Follow' => 'noindex, follow',
        'NoIndex & NoFollow' => 'noindex, nofollow',
        'No Archive' => 'noarchive',
        'No Snippet' => 'nosnippet',
    ];
    $seoRobots = $robotMap[$meta['robots'] ?? 'Index & Follow'];
@endphp

{{-- OUTPUT --}}
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="robots" content="{{ $seoRobots }}">
