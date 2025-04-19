<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'original_name',
        'file_name',
        'mime_type',
        'size',
        'folder_id',
        'alt_text',
        'caption',
    ];

    /**
     * Always include this in arrays/JSON:
     */
    protected $appends = ['url'];

    /**
     * One media can have many meta entries.
     */
    public function metas()
    {
        return $this->hasMany(MediaMeta::class);
    }

    /**
     * Belongs to a single folder.
     */
    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Polymorphic many-to-many attachments (e.g. posts, pages, products, etc.)
     */
    public function attachments()
    {
        return $this->morphToMany(
            Model::class,     // the related model superclass
            'mediable',       // pivot morph name
            'mediaables'      // pivot table
        )
            ->withPivot(['zone', 'order'])
            ->withTimestamps();
    }

    /**
     * Accessor: generates a public URL for this file
     */
    public function getUrlAttribute(): string
    {
        // ensure no leading slash
        $path = ltrim($this->file_name, '/');
        return asset("storage/{$path}");
    }
}