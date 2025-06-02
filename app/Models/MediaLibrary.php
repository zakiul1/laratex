<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('library')
            ->useDisk('public')
            ->withResponsiveImages();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        //
        // 1) JPEG/PNG conversions (always)
        //

        // — “thumbnail” remains 200×200 —
        $this
            ->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 200, 200)
            ->nonQueued();

        // — “small” remains 400×300 (400px wide cropped to 300px) —
        $this
            ->addMediaConversion('medium') // you called this “medium” before
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        // ── NEW: “mobile” at max-width: 480px (proportionally scaled) ──
        // If your layout often needs a ~480px-wide image for phones,
        // use Fit::Max so the height is calculated automatically.
        $this
            ->addMediaConversion('mobile')
            ->fit(Fit::Max, 480, 9999)   // 480px maximum width, height unconstrained
            ->quality(75)               // slightly lower quality to save bytes
            ->nonQueued();

        // ── NEW: “tablet” at max-width: 768px ──
        $this
            ->addMediaConversion('tablet')
            ->fit(Fit::Max, 768, 9999)   // 768px maximum width
            ->quality(80)
            ->nonQueued();

        // — “large” remains 1024×576 —
        $this
            ->addMediaConversion('large')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80);

        //
        // 2) WebP conversions (always)
        //

        // — thumbnail‐webp: 200×200 —
        $this
            ->addMediaConversion('thumbnail-webp')
            ->format('webp')
            ->fit(Fit::Crop, 200, 200)
            ->quality(80)
            ->nonQueued();

        // — medium‐webp: 400×300 —
        $this
            ->addMediaConversion('medium-webp')
            ->format('webp')
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        // ── NEW: mobile‐webp: max‐width 480px ──
        $this
            ->addMediaConversion('mobile-webp')
            ->format('webp')
            ->fit(Fit::Max, 480, 9999)
            ->quality(75)
            ->nonQueued();

        // ── NEW: tablet‐webp: max‐width 768px ──
        $this
            ->addMediaConversion('tablet-webp')
            ->format('webp')
            ->fit(Fit::Max, 768, 9999)
            ->quality(80)
            ->nonQueued();

        // — large‐webp: 1024×576 —
        $this
            ->addMediaConversion('large-webp')
            ->format('webp')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80);

        //
        // 3) AVIF conversions (if supported)
        //

        if (function_exists('imageavif')) {
            // — thumbnail‐avif: 200×200 —
            $this
                ->addMediaConversion('thumbnail-avif')
                ->format('avif')
                ->fit(Fit::Crop, 200, 200)
                ->quality(60)
                ->nonQueued();

            // — medium‐avif: 400×300 —
            $this
                ->addMediaConversion('medium-avif')
                ->format('avif')
                ->fit(Fit::Crop, 400, 300)
                ->quality(60)
                ->nonQueued();

            // ── NEW: mobile‐avif: max‐width 480px ──
            $this
                ->addMediaConversion('mobile-avif')
                ->format('avif')
                ->fit(Fit::Max, 480, 9999)
                ->quality(60)
                ->nonQueued();

            // ── NEW: tablet‐avif: max‐width 768px ──
            $this
                ->addMediaConversion('tablet-avif')
                ->format('avif')
                ->fit(Fit::Max, 768, 9999)
                ->quality(60)
                ->nonQueued();

            // — large‐avif: 1024×576 —
            $this
                ->addMediaConversion('large-avif')
                ->format('avif')
                ->fit(Fit::Max, 1024, 576)
                ->quality(60);
        }
    }
}