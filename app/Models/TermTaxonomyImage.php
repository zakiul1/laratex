<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermTaxonomyImage extends Model
{
    protected $table = 'term_taxonomy_images';

    protected $fillable = [
        'term_taxonomy_id',
        'media_id',
    ];

    // Automatically eager-load the Media relation
    protected $with = ['media'];

    // Cast IDs to integers
    protected $casts = [
        'id' => 'integer',
        'term_taxonomy_id' => 'integer',
        'media_id' => 'integer',
    ];

    // Expose `url` in toArray()/toJson()
    protected $appends = ['url'];

    /**
     * Link back to the Media model.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id', 'id');
    }

    /**
     * Convenience method to mirror Media::getUrl().
     *
     * @param  string|null  $size  e.g. 'thumbnail', 'medium', etc.
     * @return string
     */
    public function getUrl(string $size = null): string
    {
        return $this->media
            ? $this->media->getUrl($size)
            : '';
    }

    /**
     * Attribute accessor so you can just do $image->url.
     */
    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }
}