<?php

namespace Plugins\SliderPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    public const LAYOUT_PURE = 'pure';
    public const LAYOUT_WITH_CONTENT = 'with-content';
    public const LAYOUT_CAROUSEL = 'carousel';

    public const LAYOUTS = [
        self::LAYOUT_PURE,
        self::LAYOUT_WITH_CONTENT,
        self::LAYOUT_CAROUSEL,
    ];

    protected $fillable = [
        'name',
        'slug',
        'layout',
        'location',
        'heading',
        'slogan',
        'show_indicators',
        'show_arrows',
        'autoplay',
        'is_active',
    ];

    protected $casts = [
        'show_indicators' => 'boolean',
        'show_arrows' => 'boolean',
        'autoplay' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(SliderItem::class)
            ->orderBy('sort_order');
    }
}