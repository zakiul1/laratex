<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Image\Enums\Fit;

class Media extends BaseMedia implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        //
        // 1) JPEG/PNG “thumbnail” conversion (if you still need it)
        //
        $this
            ->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 200, 200)
            ->nonQueued();

        //
        // 2) “medium” and “large” JPEG/PNG conversions (if you still need them)
        //
        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Crop, 400, 300)
            ->quality(80)
            ->nonQueued();

        $this
            ->addMediaConversion('large')
            ->fit(Fit::Max, 1024, 576)
            ->quality(80)
            ->nonQueued();

        //
        // 3) “thumbnail-webp”, “medium-webp”, “large-webp” conversions
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
            ->quality(80)
            ->nonQueued();

        //
        // 4) If you also want AVIF, you can add “thumbnail-avif” etc. here
        //    exactly as you did for webp
        //
        // $this
        //     ->addMediaConversion('thumbnail-avif')
        //     ->format('avif')
        //     ->fit(Fit::Crop, 200, 200)
        //     ->quality(60)
        //     ->nonQueued();
        //
        // …and so on
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'media_term_taxonomy',
            'media_id',
            'term_taxonomy_id'
        )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type')
            ->withTimestamps();
    }
}