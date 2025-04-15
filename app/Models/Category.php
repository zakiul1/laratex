<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'featured_image', 'parent_id'];

    // Subcategories
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Products under a category
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}