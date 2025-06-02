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
     * E.g. [150 => 'thumbnail', 300 => 'medium', 1024 => 'large'].
     *
     * @var array<int,string>
     */
    public array $breakpoints;

    /**
     * @param SpatieMedia         $media
     * @param array<int,string>|null $breakpoints
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
        // 1) Build AVIF URLs first (if they exist as "*-avif"):
        //
        $avifUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $avifKey = "{$conversion}-avif";
            if ($this->media->hasGeneratedConversion($avifKey)) {
                $avifUrls[$width] = $this->media->getUrl($avifKey);
            }
        }

        // 1a) Optionally add "original-avif" if defined:
        // $fullPath = $this->media->getPath();
        // if ($this->media->hasGeneratedConversion('original-avif')) {
        //     $info = @getimagesize($fullPath);
        //     $origW = $info ? $info[0] : 2048;
        //     $avifUrls[$origW] = $this->media->getUrl('original-avif');
        // }

        //
        // 2) Build WebP URLs next (if they exist as "*-webp"):
        //
        $webpUrls = [];
        foreach ($this->breakpoints as $width => $conversion) {
            $webpKey = "{$conversion}-webp";
            if ($this->media->hasGeneratedConversion($webpKey)) {
                $webpUrls[$width] = $this->media->getUrl($webpKey);
            }
        }

        // 2a) Optionally add "original-webp" if defined:
        // if ($this->media->hasGeneratedConversion('original-webp')) {
        //     $info = @getimagesize($this->media->getPath());
        //     $origW = $info ? $info[0] : 2048;
        //     $webpUrls[$origW] = $this->media->getUrl('original-webp');
        // }

        //
        // 3) Sort both arrays by width ascending:
        //
        ksort($avifUrls);
        ksort($webpUrls);

        //
        // 4) Build srcset strings:
        //
        $avifSrcset = collect($avifUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        $webpSrcset = collect($webpUrls)
            ->map(fn($url, $w) => "{$url} {$w}w")
            ->implode(', ');

        //
        // 5) Choose a fallback for <img>: pick smallest AVIF if exists, otherwise smallest WebP.
        //
        if (!empty($avifUrls)) {
            $firstAvif = array_values($avifUrls)[0];
            $fallback = $firstAvif;
        } elseif (!empty($webpUrls)) {
            $firstWebp = array_values($webpUrls)[0];
            $fallback = $firstWebp;
        } else {
            // If no conversions exist at all, fall back to the original file (whatever its format)
            $fallback = $this->media->getUrl();
        }

        //
        // 6) Define a “sizes” attribute suitable to your layout:
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