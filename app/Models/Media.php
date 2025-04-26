<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * The categories (TermTaxonomy) associated with this media item.
     * Uses the WordPress term_relationships pivot table and filters by taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            TermTaxonomy::class,
            'term_relationships',    // Pivot table
            'object_id',             // Media primary key on pivot
            'term_taxonomy_id'       // TermTaxonomy primary key on pivot
        )
            ->where('taxonomy', 'media_category');
    }
}