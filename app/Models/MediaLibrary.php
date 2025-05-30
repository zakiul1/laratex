<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    /**
     * Define your media collections.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('library')
            ->useDisk('public')
            ->withResponsiveImages();
    }

    /**
     * Define your media conversions, including properly sized crops
     * and next-gen formats (WebP/AVIF).
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // 1) Thumbnail: crop to exactly 200×200
        $this
            ->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 200, 200)
            ->nonQueued();

        // 2) Medium: crop to 400×300
        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        // 3) Large: max 1024×576 (keep aspect ratio)
        $this
            ->addMediaConversion('large')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80);

        // 4) Generate next-gen WebP & AVIF for each size
        foreach (['thumbnail', 'medium', 'large'] as $size) {
            // WebP version
            $this
                ->addMediaConversion("{$size}-webp")
                ->format('webp')
                ->quality(80)
                ->nonQueued();

            // AVIF version
            $this
                ->addMediaConversion("{$size}-avif")
                ->format('avif')
                ->quality(60)
                ->nonQueued();
        }
    }
}