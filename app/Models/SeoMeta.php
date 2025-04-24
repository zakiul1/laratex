<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';
    protected $guarded = ['id'];

    // so we can cast meta to array automatically
    protected $casts = [
        'meta' => 'array',
    ];

    public function metable()
    {
        return $this->morphTo();
    }
}