<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermTaxonomy extends Model
{
    // -------------------------------
    // Primary key configuration
    // -------------------------------
    protected $primaryKey = 'term_taxonomy_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // -------------------------------
    // Eager-load images by default
    // -------------------------------
    protected $with = ['images'];

    // -------------------------------
    // Auto-append custom attributes
    // -------------------------------
    protected $appends = [
        'image_urls',
    ];

    // -------------------------------
    // Mass assignable attributes
    // -------------------------------
    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
        'status', // if you have this column
    ];

    // -------------------------------
    // Relationships
    // -------------------------------

    /** The base term record. */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'id');
    }

    /** (Optional) Parent taxonomy in the same table. */
    public function parentTaxonomy(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent',
            'term_taxonomy_id'
        );
    }

    /**
     * All Media attached via the `term_relationships` pivot.
     * (If you still need to support your old pivot table.)
     */
    public function media(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Media::class,
                'term_relationships',
                'term_taxonomy_id',
                'object_id'
            )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type');
    }

    /**
     * A dedicated images table (term_taxonomy_images) if you need
     * to store extra metadata (e.g. cropping, ordering, etc).
     */
    public function images(): HasMany
    {
        return $this->hasMany(
            TermTaxonomyImage::class,
            'term_taxonomy_id',   // FK on images table
            'term_taxonomy_id'    // Local key on this model
        );
    }

    // -------------------------------
    // Custom Attributes
    // -------------------------------

    /**
     * Returns a simple array of all attached image URLs.
     */
    public function getImageUrlsAttribute(): array
    {
        // assumes TermTaxonomyImage has a `url` accessor
        return $this->images
            ->pluck('url')
            ->filter()
            ->values()
            ->all();
    }
}