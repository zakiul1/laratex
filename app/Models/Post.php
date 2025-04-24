<?php
namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSeoMeta;
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'type',
        'status',
        'template',
        'featured_image',
        'author_id'
    ];

    public function meta()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getMeta($key, $default = null)
    {
        $meta = $this->meta->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }
}