<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasSeoMeta;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'type',
        'status',
        'template',
        'featured_images', // JSON array of media IDs
        'author_id',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'featured_images' => 'array',
    ];

    /**
     * All stored meta entries for this post.
     */
    public function meta(): HasMany
    {
        return $this->hasMany(PostMeta::class);
    }

    /**
     * Convenient getter for a single meta key.
     */
    public function getMeta(string $key, $default = null)
    {
        $meta = $this->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : $default;
    }

    /**
     * Post author relationship.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * All term_taxonomies attached to this post (any taxonomy).
     */
    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'term_relationships',
            'object_id',
            'term_taxonomy_id'
        )
            ->withPivot('object_type')
            ->wherePivot('object_type', 'post');
    }

    /**
     * Only the 'category' terms.
     */
    public function categories(): BelongsToMany
    {
        return $this->terms()->where('taxonomy', 'category');
    }

    /**
     * Only the 'tag' terms.
     */
    public function tags(): BelongsToMany
    {
        return $this->terms()->where('taxonomy', 'tag');
    }

    /**
     * Sync categories by taxonomy IDs.
     */
    public function syncCategories(array $ids): void
    {
        $this->categories()->sync($ids);
    }

    /**
     * Sync tags by taxonomy IDs.
     */
    public function syncTags(array $ids): void
    {
        $this->tags()->sync($ids);
    }
}