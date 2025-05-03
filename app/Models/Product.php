<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'status',
        // no legacy single-path column needed here
    ];

    /**
     * One-to-many: gallery images (if used).
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Many-to-many: product ⇄ categories.
     */
    public function taxonomies()
    {
        return $this->morphToMany(
            TermTaxonomy::class,
            'object',
            'term_relationships',
            'object_id',
            'term_taxonomy_id'
        );
    }

    /**
     * Many-to-many pivot to the Media model for featured images.
     */
    public function featuredMedia()
    {
        return $this->belongsToMany(
            Media::class,
            'product_media',    // pivot table
            'product_id',
            'media_id'
        );
    }
}