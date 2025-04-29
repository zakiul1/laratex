<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TermTaxonomyImage extends Model
{
    protected $table = 'term_taxonomy_images';
    protected $fillable = ['term_taxonomy_id', 'path'];

    public function taxonomy()
    {
        return $this->belongsTo(TermTaxonomy::class, 'term_taxonomy_id', 'term_taxonomy_id');
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}