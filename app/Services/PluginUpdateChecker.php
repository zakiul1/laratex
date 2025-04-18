<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class PluginUpdateChecker
{
    public function fetchAvailableUpdates(): array
    {
        $updates = [];

        foreach (Plugin::all() as $plugin) {
            $path = base_path("plugins/{$plugin->slug}/plugin.json");

            if (!File::exists($path))
                continue;

            $meta = json_decode(File::get($path), true);
            if (empty($meta['update_url']))
                continue;

            try {
                $response = Http::timeout(5)->get($meta['update_url']);

                if ($response->ok()) {
                    $remote = $response->json();
                    $remoteVersion = $remote['version'] ?? null;

                    if ($remoteVersion && version_compare($remoteVersion, $plugin->version, '>')) {
                        $updates[$plugin->slug] = [
                            'version' => $remoteVersion,
                            'changelog' => $remote['changelog'] ?? '',
                            'download_url' => $remote['download_url'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Log error or skip silently
                \Log::warning("Plugin update check failed for {$plugin->slug}: {$e->getMessage()}");
            }
        }

        return $updates;
    }
}