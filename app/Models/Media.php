<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
// alias the Spatie model so we can refer to it in the method signature
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
    ];

    /**
     * Register your media conversions.
     *
     * Must accept ?SpatieMedia to match the interface exactly.
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        // A small square thumbnail
        $this
            ->addMediaConversion('thumbnail')
            ->width(200)
            ->height(200)
            ->fit('crop', 200, 200)
            ->nonQueued();  // remove nonQueued() if you want to queue conversions
    }

    /**
     * Define a pivot relation to media categories.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                TermTaxonomy::class,
                'term_relationships',
                'object_id',
                'term_taxonomy_id'
            )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type');
    }
}