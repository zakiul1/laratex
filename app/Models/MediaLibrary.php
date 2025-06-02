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

        $this
            ->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 200, 200)
            ->nonQueued();

        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        $this
            ->addMediaConversion('large')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80);

        //
        // 2) WebP conversions (always)
        //

        $this
            ->addMediaConversion('thumbnail-webp')
            ->format('webp')
            ->fit(Fit::Crop, 200, 200)
            ->quality(80)
            ->nonQueued();

        $this
            ->addMediaConversion('medium-webp')
            ->format('webp')
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        $this
            ->addMediaConversion('large-webp')
            ->format('webp')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80);
    }
}