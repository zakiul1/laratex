<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductImage;
use App\Models\TermTaxonomy;
use App\Models\Media;

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
        'featured_image',    // legacy single‐path column (optional)
        // no category_id here
    ];

    /**
     * Raw gallery uploads (one‐to‐many).
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Many‐to‐many term_taxonomy (your categories).
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
     * Multiple “featured” Media items (via product_media pivot).
     */
    public function featuredMedia()
    {
        return $this->belongsToMany(
            Media::class,
            'product_media',    // pivot table: product_id, media_id
            'product_id',
            'media_id'
        );
    }

    /**
     * Helper for the old single‐image column (if you still need it).
     */
    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }
}