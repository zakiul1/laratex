<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Media;
use App\Models\TermTaxonomy;
use App\Models\PostMeta;
use App\Models\User;

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
        'featured_images',
    ];

    protected $casts = [
        'featured_images' => 'array',
    ];

    /*-----------------------------------------
     | Relationships
     |-----------------------------------------*/

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * All the TermTaxonomy entries attached to this post,
     * filtered on object_type = 'post'.
     */
    public function categories()
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'term_relationships',  // pivot table
            'object_id',           // this model’s FK in pivot
            'term_taxonomy_id'     // related model’s FK
        )
            ->wherePivot('object_type', 'post')
            ->withPivot('object_type')
            ->withTimestamps();
    }

    /**
     * Alias for plugin queries.
     */
    public function termTaxonomies()
    {
        return $this->categories();
    }

    /**
     * (Optional) Another alias if you prefer `->taxonomies()`.
     */
    public function taxonomies()
    {
        return $this->categories();
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

    /*-----------------------------------------
     | Featured Images Helpers
     |-----------------------------------------*/

    public function getFeaturedImageIdsAttribute(): array
    {
        return $this->featured_images ?? [];
    }

    public function syncFeaturedImages(array $ids): void
    {
        $this->featured_images = array_values($ids);
        $this->save();
    }

    public function getFeaturedMediaAttribute()
    {
        $ids = $this->featured_images ?? [];
        if (empty($ids)) {
            return collect();
        }

        return Media::whereIn('id', $ids)->get();
    }

    /*-----------------------------------------
     | Categories Helper
     |-----------------------------------------*/

    /**
     * Sync the given category IDs against this post.
     */
    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    /**
     * Legacy terms() relation—if other parts of Laratex CMS still use it.
     */
    public function terms()
    {
        return $this->belongsToMany(
            \App\Models\Term::class,
            'term_post',
            'post_id',
            'term_id'
        );
    }
}