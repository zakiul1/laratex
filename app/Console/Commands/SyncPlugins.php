<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Plugin;

class SyncPlugins extends Command
{
    protected $signature = 'plugins:sync';
    protected $description = 'Sync all plugins from the /plugins folder';

    public function handle()
    {
        $folders = File::directories(base_path('plugins'));

        foreach ($folders as $folder) {
            $slug = basename($folder);
            $jsonPath = $folder . '/plugin.json';

            if (!File::exists($jsonPath))
                continue;

            $meta = json_decode(File::get($jsonPath), true);

            Plugin::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'] ?? $slug,
                    'description' => $meta['description'] ?? '',
                    'version' => $meta['version'] ?? '1.0.0',
                ]
            );
        }

        $this->info('Plugins synced.');
    }
}