<?php

namespace App\Traits;

use App\Models\SeoMeta;

trait HasSeoMeta
{
    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'metable');
    }

    /**
     * Shortcut to get a field or fallback.
     */
    public function seo(string $key, $default = null)
    {
        return data_get($this->seoMeta->meta, $key, $default);
    }
}