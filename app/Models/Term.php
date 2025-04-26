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
}