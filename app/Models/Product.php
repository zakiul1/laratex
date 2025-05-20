<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // in App\Models\Product
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',  // ← newly added
        'price',
        'stock',
        'status',
    ];


    /**
     * One-to-many relation for gallery images.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Many-to-many relation to categories (term_taxonomies) via pivot.
     * Filters on object_type = 'product'.
     *
     * @return BelongsToMany
     */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'term_relationships',  // pivot table
            'object_id',           // this model's FK
            'term_taxonomy_id'     // foreign key on pivot
        )
            ->wherePivot('object_type', 'product')
            ->withPivot('object_type')
            ->withTimestamps();
    }

    /**
     * Many-to-many pivot to the Media model for featured images.
     *
     * @return BelongsToMany
     */
    public function featuredMedia(): BelongsToMany
    {
        return $this->belongsToMany(
            Media::class,
            'product_media',    // pivot table
            'product_id',       // this model's FK
            'media_id'          // foreign key on pivot
        );
    }
}