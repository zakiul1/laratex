<?php

namespace Plugins\RibbonPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderRibbon extends Model
{
    protected $table = 'header_ribbons';

    protected $fillable = [
        'left_text',
        'center_text',
        'phone',
        'email',
        'bg_color',
        'text_color',
        'height',
        'is_active',
    ];
}