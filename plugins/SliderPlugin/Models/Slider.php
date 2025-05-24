<?php
namespace Plugins\SliderPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'layout',
        'location',
        'heading',      // ← new
        'slogan',       // ← new
        'show_indicators',
        'show_arrows',
        'autoplay',
        'is_active'
    ];

    public function items()
    {
        return $this->hasMany(SliderItem::class)
            ->orderBy('sort_order');
    }
}