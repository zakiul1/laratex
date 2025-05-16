<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = ['name', 'slug'];

    public function taxonomies()
    {
        return $this->hasMany(TermTaxonomy::class);
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'term_post', 'term_id', 'post_id');
    }
}