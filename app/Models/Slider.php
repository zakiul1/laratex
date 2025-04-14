<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'button_text',
        'button_url',
        'layout',
        'image_position',
        'show_arrows',
        'show_indicators',
        'slider_location',
    ];

    protected $casts = [
        'show_arrows' => 'boolean',
        'show_indicators' => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(SliderImage::class);
    }
}