<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginSetting extends Model
{
    protected $fillable = ['plugin_slug', 'key', 'value'];

    public $timestamps = true;

    protected $casts = [
        'value' => 'array', // Supports JSON
    ];
}