<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class ResponsiveImage extends Component
{
    /**
     * The Spatie Media instance that this component will render.
     *
     * @var SpatieMedia
     */
    public SpatieMedia $media;

    /**
     * An associative array of [width => conversionName].
     * Example: [150 => 'thumbnail', 300 => 'medium', 1024 => 'large'].
     *
     * @var array<int,string>
     */
    public array $breakpoints;

    /**
     * @param  SpatieMedia             $media
     * @param  array<int,string>|null  $breakpoints
     */
    public function __construct(SpatieMedia $media, array $breakpoints = null)
    {
        $this->media = $media;

        // Default breakpoints if none provided:
        $this->breakpoints = $breakpoints ?? [
            150 => 'thumbnail',
            300 => 'medium',
            1024 => 'large',
        ];
    }

    public function render()
    {
        //
        // 1) Build AVIF URLs (if they exist as "<conversion>-avif")
        //
        $avifUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $avifKey = "{$conversion}-avif";
            if ($this->media->hasGeneratedConversion($avifKey)) {
                $avifUrls[$width] = $this->media->getUrl($avifKey);
            }
        }

        //
        // 2) Build WebP URLs (if they exist as "<conversion>-webp")
        //
        $webpUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $webpKey = "{$conversion}-webp";
            if ($this->media->hasGeneratedConversion($webpKey)) {
                $webpUrls[$width] = $this->media->getUrl($webpKey);
            }
        }

        //
        // 3) Sort both AVIF‐ and WebP‐URL arrays by width ascending
        //
        ksort($avifUrls);
        ksort($webpUrls);

        //
        // 4) Build “srcset” strings from each associative array
        //
        $avifSrcset = collect($avifUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        $webpSrcset = collect($webpUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        //
        // 5) Choose a fallback “src” for <img>:
        //    – If any AVIF exists, use the smallest‐width AVIF
        //    – Otherwise, if any WebP exists, use the smallest‐width WebP
        //    – Otherwise, fall back to the original file URL (whatever format)
        //
        if (!empty($avifUrls)) {
            // pick the first (smallest) AVIF
            $fallback = array_values($avifUrls)[0];
        } elseif (!empty($webpUrls)) {
            // pick the first (smallest) WebP
            $fallback = array_values($webpUrls)[0];
        } else {
            // no conversions—just use the original
            $fallback = $this->media->getUrl();
        }

        //
        // 6) Define a “sizes” attribute appropriate for most responsive layouts:
        //
        $sizes = '(max-width: 640px) 150px, (max-width: 1024px) 300px, 1024px';

        return view('components.responsive-image', [
            'fallback' => $fallback,
            'avifSrcset' => $avifSrcset,
            'webpSrcset' => $webpSrcset,
            'sizes' => $sizes,
        ]);
    }
}