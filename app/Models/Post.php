<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'type',
        'status',
        'template',
        'author_id',
        'featured_images',       // ← add this
    ];

    protected $casts = [
        'featured_images' => 'array',  // ← cast JSON→array
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'term_relationships',
            'object_id',
            'term_taxonomy_id'
        );
    }

    public function meta()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function seoMeta()
    {
        return $this->hasOne(PostMeta::class)
            ->where('meta_key', 'seo');
    }

    /**
     * Accessor to get the array of featured image IDs.
     */
    public function getFeaturedImageIdsAttribute(): array
    {
        // returns [] if null
        return $this->featured_images ?? [];
    }

    /**
     * Sync the featured image IDs by overwriting the JSON column.
     */
    public function syncFeaturedImages(array $ids): void
    {
        $this->featured_images = $ids;
        $this->save();
    }

    /**
     * Sync categories by IDs.
     */
    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }
}