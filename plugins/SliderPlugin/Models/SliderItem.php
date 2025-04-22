<?php
namespace Plugins\SliderPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class SliderItem extends Model
{
    protected $fillable = [
        'slider_id',
        'image_path',
        'content',
        'sort_order'
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}