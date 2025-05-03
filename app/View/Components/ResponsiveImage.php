<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\Image\Enums\Fit;

class ResponsiveImage extends Component
{
    public SpatieMedia $media;
    public array $breakpoints;

    public function __construct(SpatieMedia $media, array $breakpoints = null)
    {
        $this->media = $media;
        // width => media conversion name, with an empty string meaning “original”
        $this->breakpoints = $breakpoints ?? [
            150 => 'thumbnail',
            300 => 'medium',
            1024 => 'large',
            // original will be added below
        ];
    }

    public function render()
    {
        // build URLs
        $urls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            if ($conversion === '') {
                $urls[$width] = $this->media->getUrl();
            } else {
                $urls[$width] = $this->media->hasGeneratedConversion($conversion)
                    ? $this->media->getUrl($conversion)
                    : $this->media->getUrl();
            }
        }

        // now handle the original file size
        $originalUrl = $this->media->getUrl();
        $fullPath = $this->media->getPath();  // absolute path to the original on disk

        if (file_exists($fullPath) && $info = @getimagesize($fullPath)) {
            $originalWidth = $info[0];
        } else {
            $originalWidth = 2048; // fallback width
        }

        $urls[$originalWidth] = $originalUrl;

        // build srcset string
        $srcset = collect($urls)
            ->map(fn($url, $width) => "{$url} {$width}w")
            ->implode(', ');

        // pick a good fallback src (medium)
        $fallback = $urls[300] ?? reset($urls);

        // define sizes attribute
        $sizes = '(max-width:640px) 150px, (max-width:1024px) 300px, 1024px';

        return view('components.responsive-image', compact('fallback', 'srcset', 'sizes'));
    }
}