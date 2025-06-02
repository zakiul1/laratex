<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\Media;
use App\Models\ProductMeta;
use App\Models\TermTaxonomy;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
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
            'term_relationships',   // pivot table
            'object_id',            // this model’s FK
            'term_taxonomy_id'      // related model’s FK
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
            'product_media',       // pivot table
            'product_id',          // this model’s FK
            'media_id'             // related model’s FK
        );
    }

    /**
     * One-to-many relation for all custom meta rows (e.g. product_meta table).
     * Includes both SEO entries (meta_key = 'seo') and any other keys.
     *
     * @return HasMany
     */
    public function meta(): HasMany
    {
        return $this->hasMany(ProductMeta::class);
    }

    /**
     * One-to-one relation for the single “seo” JSON blob.
     * Only returns the row where meta_key = 'seo'.
     *
     * @return HasOne
     */
    public function seoMeta(): HasOne
    {
        return $this->hasOne(ProductMeta::class)
            ->where('meta_key', 'seo');
    }

    /**
     * Convenience accessor: $product->seo returns the decoded array from product_meta->meta_value.
     *
     * @return array
     */
    public function getSeoAttribute(): array
    {
        if (!$row = $this->seoMeta()->first()) {
            return [];
        }

        return is_string($row->meta_value)
            ? (json_decode($row->meta_value, true) ?? [])
            : [];
    }
}