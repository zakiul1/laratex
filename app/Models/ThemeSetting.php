<?php

// app/Models/ThemeSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $casts = [
        'show_tagline' => 'boolean',
        'options' => 'array',  // <-- JSON → PHP array
    ];

    // you can keep your old columns fillable,
    // but if you prefer to move everything into options, remove them here
    protected $fillable = [

        'theme',
        'logo',
        'primary_color',
        'font_family',
        'footer_text',
        'custom_css',
        'options',
    ];
}