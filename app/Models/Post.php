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
            'object_id',            // ← change here
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

    public function getFeaturedImageIdsAttribute()
    {
        return $this->meta()
            ->where('meta_key', 'featured_image')
            ->pluck('meta_value')
            ->map(fn($v) => (int) $v)
            ->toArray();
    }

    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    public function syncFeaturedImages(array $ids): void
    {
        $this->meta()
            ->where('meta_key', 'featured_image')
            ->delete();

        foreach ($ids as $mediaId) {
            $this->meta()->create([
                'meta_key' => 'featured_image',
                'meta_value' => $mediaId,
            ]);
        }
    }
}