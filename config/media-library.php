<?php

use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

return [
    /*
     * The model that should be used for media items.
     */
    'media_model' => \App\Models\Media::class,

    /*
     * The disk on which MediaLibrary will store originals & conversions.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * If you want conversions built synchronously (instead of queued),
     * set this to `false`. That way the thumbnail file will appear
     * immediately and won’t require a running queue worker.
     */
    'queue_conversions_by_default' => false,

    /*
     * The folder name where conversions will be placed, relative to
     * the root of the disk.
     */
    'conversion_suffix' => 'conversions',

    /*
     * This class generates the subdirectory and filename for each
     * original and conversion.  The default will put them under:
     *
     *    storage/app/public/{media_id}/conversions/{conversionFileName}
     */
    'path_generator' => DefaultPathGenerator::class,

    /*
     * This URL generator will prepend your APP_URL + /storage to any
     * path, so that ->getUrl('thumbnail') returns something like:
     *
     *   http://localhost:8000/storage/4/conversions/…-thumbnail.jpg
     */
    'url_generator' => DefaultUrlGenerator::class,

    /*
     * (The rest of your optimizer, jobs, responsive images, etc remain as you had them.)
     */
];