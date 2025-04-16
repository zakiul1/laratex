<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'logo',
        'show_ribbon',
        'ribbon_left_text',
        'ribbon_phone',
        'ribbon_email',
        'ribbon_bg_color',
        'ribbon_text_color',
        'extra',
    ];

    protected $casts = [
        'show_ribbon' => 'boolean',
        'extra' => 'array',
    ];
    public static function activeTheme()
    {
        return self::first()?->active_theme ?? 'default';
    }

}