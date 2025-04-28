<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermTaxonomy extends Model
{
    protected $primaryKey = 'term_taxonomy_id';

    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'term_relationships', 'term_taxonomy_id', 'object_id')
            ->where('object_type', 'media');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent');
    }
}