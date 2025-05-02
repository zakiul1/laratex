<?php
// app/Models/MediaLibrary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('library')
            ->useDisk('public')
            ->withResponsiveImages();
    }

    public function registerMediaConversions(SpatieMedia $media = null): void
    {
        // 150×150 square thumbnail, generated immediately
        $this
            ->addMediaConversion('thumbnail')
            ->width(150)
            ->height(150)
            ->crop('crop-center', 150, 150) // center-crop
            ->sharpen(10)
            ->nonQueued();

        // 300×300 max medium
        $this
            ->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        // 1024×1024 max large
        $this
            ->addMediaConversion('large')
            ->width(1024)
            ->height(1024);
    }
}