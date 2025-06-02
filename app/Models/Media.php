<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use App\Models\TermTaxonomy;

class Media extends BaseMedia implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Register all of the conversions we need, in both JPEG/PNG and WebP formats.
     */
    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        //
        // 1) JPEG/PNG conversions (always generate synchronously)
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
            ->quality(80)
            ->nonQueued();

        //
        // 2) WebP conversions (same sizes, but output as .webp)
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
    }

    /**
     * All the taxonomy categories this media item belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,    // related model
            'media_term_taxonomy',  // pivot table
            'media_id',             // this model’s FK in pivot
            'term_taxonomy_id'      // related model’s FK in pivot
        )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type')
            ->withTimestamps();
    }
}