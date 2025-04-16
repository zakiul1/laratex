<?php

// app/Models/ThemeSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $fillable = [
        'theme', 'logo', 'primary_color', 'custom_css',
        'font_family', 'footer_text',
    ];
    
}
