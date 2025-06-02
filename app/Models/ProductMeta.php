<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMeta extends Model
{
    // Explicit table name, since Laravel would otherwise look for "product_metas"
    protected $table = 'product_meta';

    // Allow mass‐assignment on these three fields
    protected $fillable = [
        'product_id',
        'meta_key',
        'meta_value',
    ];

    /**
     * Inverse relationship: each meta row belongs to one Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}