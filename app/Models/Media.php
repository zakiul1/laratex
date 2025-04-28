<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    public function categories()
    {
        return $this->belongsToMany(TermTaxonomy::class, 'term_relationships', 'object_id', 'term_taxonomy_id')
            ->where('object_type', 'media')
            ->withPivot('object_type');
    }
}