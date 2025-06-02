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
     * Example: [150 => 'thumbnail', 300 => 'medium', 480 => 'mobile', 768 => 'tablet', 1024 => 'large'].
     *
     * @var array<int,string>
     */
    public array $breakpoints;

    /**
     * The sizes attribute for the <img> tag (e.g. "(max-width:640px) 100vw, (max-width:1024px) 60vw, 50vw").
     *
     * @var string
     */
    public string $sizes;

    /**
     * Optional <img> loading attribute: "eager" or "lazy".
     *
     * @var string|null
     */
    public ?string $loading;

    /**
     * Optional <img> fetchpriority attribute: "high", "low", etc.
     *
     * @var string|null
     */
    public ?string $fetchpriority;

    /**
     * @param  SpatieMedia            $media
     * @param  array<int,string>|null $breakpoints
     * @param  string|null            $sizes
     * @param  string|null            $loading
     * @param  string|null            $fetchpriority
     */
    public function __construct(
        SpatieMedia $media,
        array $breakpoints = null,
        string $sizes = null,
        string $loading = null,
        string $fetchpriority = null
    ) {
        $this->media = $media;

        // 1) Default breakpoints if none provided:
        //    – 150×150 (thumbnail)
        //    – 300×300 (medium)
        //    – 480×auto (mobile)
        //    – 768×auto (tablet)
        //    – 1024×576 (large)
        $this->breakpoints = $breakpoints ?? [
            150 => 'thumbnail',
            300 => 'medium',
            480 => 'mobile',
            768 => 'tablet',
            1024 => 'large',
        ];

        // 2) Default sizes attribute if none provided:
        //    Adjust these media conditions as needed in your layouts.
        $this->sizes = $sizes ?? '(max-width: 640px) 100vw, (max-width: 1024px) 60vw, 50vw';

        // 3) Loading / fetchpriority can be overridden per-instance
        $this->loading = $loading ?? null;
        $this->fetchpriority = $fetchpriority ?? null;
    }

    public function render()
    {
        //
        // 4) Build AVIF URLs (if they exist as "<conversion>-avif")
        //
        $avifUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $avifKey = "{$conversion}-avif";
            if ($this->media->hasGeneratedConversion($avifKey)) {
                $avifUrls[$width] = $this->media->getUrl($avifKey);
            }
        }

        //
        // 5) Build WebP URLs (if they exist as "<conversion>-webp")
        //
        $webpUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $webpKey = "{$conversion}-webp";
            if ($this->media->hasGeneratedConversion($webpKey)) {
                $webpUrls[$width] = $this->media->getUrl($webpKey);
            }
        }

        //
        // 6) Sort both AVIF‐ and WebP‐URL arrays by width ascending
        //
        ksort($avifUrls);
        ksort($webpUrls);

        //
        // 7) Build “srcset” strings from each associative array
        //
        $avifSrcset = collect($avifUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        $webpSrcset = collect($webpUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        //
        // 8) Choose a fallback “src” for <img>:
        //    – If any AVIF exists, use the smallest‐width AVIF
        //    – Otherwise, if any WebP exists, use the smallest‐width WebP
        //    – Otherwise, fall back to the original file URL (JPEG/PNG/etc)
        //
        if (!empty($avifUrls)) {
            // pick the first (smallest) AVIF
            $fallback = array_values($avifUrls)[0];
        } elseif (!empty($webpUrls)) {
            // pick the first (smallest) WebP
            $fallback = array_values($webpUrls)[0];
        } else {
            // no conversions—just use the original full‐size URL
            $fallback = $this->media->getUrl();
        }

        return view('components.responsive-image', [
            'fallback' => $fallback,
            'avifSrcset' => $avifSrcset,
            'webpSrcset' => $webpSrcset,
            'sizes' => $this->sizes,
            'loading' => $this->loading,
            'fetchpriority' => $this->fetchpriority,
        ]);
    }
}