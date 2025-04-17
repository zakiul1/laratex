<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = [
        'title',
        'content',
        'widget_type',
        'widget_area',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}