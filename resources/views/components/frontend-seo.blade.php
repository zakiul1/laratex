{{-- resources/views/components/frontend-seo.blade.php --}}
@php
    use Illuminate\Support\Str;

    // Retrieve the stored SEO meta for this model (or an empty array if none exists)
    $meta = $model->seoMeta->meta ?? [];

    // 1) Compute the <title>: user‐entered SEO title, or fallback to model title, or app name
    $title = $meta['title'] ?? ($model->title ?? config('app.name'));

    // 2) Compute a non‐empty <meta name="description">:
    if (!empty($meta['description'])) {
        // Use exactly the SEO description the user provided
        $description = $meta['description'];
    } elseif (!empty($model->excerpt)) {
        // Fallback to the model’s excerpt (truncate to ~155 characters)
        $description = Str::limit(strip_tags($model->excerpt), 155, '…');
    } else {
        // Final fallback: strip HTML tags from description or content, then truncate
        $raw = $model->description ?? ($model->content ?? '');
        $description = Str::limit(strip_tags($raw), 155, '…');
    }

    // 3) Keywords (user‐entered SEO keywords, or empty)
    $keywords = $meta['keywords'] ?? '';

    // 4) Map friendly “robots” labels to actual directives
    $robotMap = [
        'Index & Follow' => 'index, follow',
        'NoIndex & Follow' => 'noindex, follow',
        'NoIndex & NoFollow' => 'noindex, nofollow',
        'No Archive' => 'noarchive',
        'No Snippet' => 'nosnippet',
    ];
    $robots = $robotMap[$meta['robots'] ?? 'Index & Follow'];
@endphp

{{-- Output the SEO tags --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="{{ $robots }}">
