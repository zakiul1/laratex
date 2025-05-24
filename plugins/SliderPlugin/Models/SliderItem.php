<?php
namespace Plugins\SliderPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Media;

class SliderItem extends Model
{
    protected $fillable = [
        'slider_id',
        'image_path',
        'media_id',         // ← new
        'content',
        'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}