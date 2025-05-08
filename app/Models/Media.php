<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use App\Models\TermTaxonomy;

class Media extends BaseMedia
{
    /**
     * Register your conversions (we keep the 200×200 thumbnail).
     */
    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        $this
            ->addMediaConversion('thumbnail')
            ->width(200)
            ->height(200)
            ->fit('crop', 200, 200)
            ->nonQueued();
    }

    /**
     * All the taxonomy categories this media item belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'media_term_taxonomy',   // pivot table
            'media_id',              // this model’s FK
            'term_taxonomy_id'       // taxonomy FK
        )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type')
            ->withTimestamps();
    }
}