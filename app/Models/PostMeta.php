<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{
    protected $fillable = ['post_id', 'meta_key', 'meta_value'];

    protected $casts = [
        'meta_value' => 'string',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getMetaValueAttribute($value)
    {
        if ($this->meta_key === 'seo') {
            return json_decode($value, true);
        }
        return $value;
    }

    public function setMetaValueAttribute($value)
    {
        if ($this->meta_key === 'seo' && is_array($value)) {
            $this->attributes['meta_value'] = json_encode($value);
        } else {
            $this->attributes['meta_value'] = $value;
        }
    }
}