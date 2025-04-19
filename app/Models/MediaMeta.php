<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaMeta extends Model
{
    protected $table = 'media_meta';
    protected $fillable = ['media_id', 'meta_key', 'meta_value'];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}