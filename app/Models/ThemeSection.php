<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSection extends Model
{
    protected $fillable = ['theme', 'key', 'title', 'order', 'settings'];
    protected $casts = [
        'settings' => 'array',
    ];
}

