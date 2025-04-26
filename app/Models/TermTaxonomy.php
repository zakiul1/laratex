<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TermTaxonomy extends Model
{
    use HasSeoMeta;

    protected $table = 'term_taxonomies';
    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
        'status',          // if you added status
        'featured_image',  // if you added featured_image
    ];

    /**
     * The term definition (name, slug, etc.)
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Posts associated with this taxonomy term.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Post::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        )
            ->wherePivot('object_type', 'post');
    }

    /**
     * Media items associated with this taxonomy term.
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Media::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        )
            ->wherePivot('object_type', 'media');
    }
}