<?php

use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use App\MediaLibrary\PublicUrlGenerator;

return [

    /*
     * The model that should be used for media items.
     */
    'media_model' => \App\Models\Media::class,

    /*
     * ---------------------------------------------------------
     * Filesystem disk name
     * ---------------------------------------------------------
     *   - `disk_name` is used by v9
     *   - `defaultFilesystem` is used by v10+
     */
    'disk_name' => env('MEDIA_DISK', 'public'),
    'defaultFilesystem' => env('MEDIA_DISK', 'public'),

    /*
     * If you want conversions built synchronously (instead of queued),
     * set this to `false`. That way thumbnails appear immediately
     * and won’t require a running queue worker.
     */
    'queue_conversions_by_default' => false,
    'driver' => 'imagick',

    /*
     * Folder name for conversions, relative to the disk root.
     */
    'conversion_suffix' => 'conversions',

    /*
     * PathGenerator: controls how files are organized on disk.
     */
    'path_generator' => DefaultPathGenerator::class,

    /*
     * URL Generator: must always return a real disk name.
     * We point this at your custom subclass.
     */
    'url_generator' => PublicUrlGenerator::class,

    /*
     * …any other settings (optimizers, responsive images, etc.)…
     */
];