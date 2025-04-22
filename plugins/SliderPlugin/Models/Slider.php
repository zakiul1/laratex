<?php
namespace Plugins\SliderPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'layout',
        'show_indicators',
        'show_arrows',
        'autoplay',
        'location',
        'is_active'
    ];

    public function items()
    {
        return $this->hasMany(SliderItem::class)
            ->orderBy('sort_order');
    }
}