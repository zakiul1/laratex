<?php
namespace App\MediaLibrary;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class PublicUrlGenerator extends DefaultUrlGenerator
{
    /** 
     * Never return null here—fall back to “public” if the config wasn’t set.
     */
    protected function getDiskName(): string
    {
        // v10 key:
        if (isset($this->config['defaultFilesystem'])) {
            return $this->config['defaultFilesystem'];
        }
        // (older versions)
        if (isset($this->config['disk_name'])) {
            return $this->config['disk_name'];
        }
        return 'public';
    }
}