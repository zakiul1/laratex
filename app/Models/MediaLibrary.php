<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
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
     * Define your media conversions.
     */
    public function registerMediaConversions(SpatieMedia $media = null): void
    {
        // Thumbnail: scale entire image into 150x150 box, pad with white
        $this
            ->addMediaConversion('thumbnail')
            ->nonQueued()                     // generate immediately
            ->fit(Fit::Contain, 150, 150)     // no crop, preserve aspect ratio
            ->background('ffffff');           // white fill for empty space

        // Medium: max 300×300, maintain aspect ratio
        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Max, 300, 300)
            ->sharpen(10);

        // Large: max 1024×1024, maintain aspect ratio
        $this
            ->addMediaConversion('large')
            ->fit(Fit::Max, 1024, 1024);
    }
}