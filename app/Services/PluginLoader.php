<?php
// app/Services/PluginLoader.php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Plugin;

class PluginLoader
{
    /**
     * Scan plugins/ for plugin.json → sync slug, provider, name, description, version & enabled flag.
     */
    public function sync(): void
    {
        $folders = File::directories(base_path('plugins'));

        foreach ($folders as $folder) {
            $slug = basename($folder);
            $jsonPath = "{$folder}/plugin.json";

            if (!File::exists($jsonPath)) {
                continue;
            }

            $meta = json_decode(File::get($jsonPath), true);
            $provider = $meta['provider'] ?? null;

            // skip if no valid provider class
            if (!$provider || !class_exists($provider)) {
                Log::warning("[PluginLoader] Skipped '{$slug}': invalid or missing provider");
                continue;
            }

            // firstOrNew so we preserve the existing `enabled` flag
            $plugin = Plugin::firstOrNew(['slug' => $slug]);

            // persist provider so bootEnabled() can register it
            $plugin->provider = $provider;
            $plugin->name = $meta['name'] ?? $slug;
            $plugin->description = $meta['description'] ?? $plugin->description;
            $plugin->version = $meta['version'] ?? $plugin->version ?? '1.0.0';

            // brand-new plugins default to disabled
            if (is_null($plugin->enabled)) {
                $plugin->enabled = false;
            }

            $plugin->save();
        }
    }

    /**
     * For every plugin marked "enabled", register its provider, run its migrations, publish assets.
     */
    public function bootEnabled(): void
    {
        $enabled = Plugin::where('enabled', true)->get();

        foreach ($enabled as $plugin) {
            $slug = $plugin->slug;
            $basePath = base_path("plugins/{$slug}");
            $jsonPath = "{$basePath}/plugin.json";

            if (!File::exists($jsonPath)) {
                continue;
            }

            $meta = json_decode(File::get($jsonPath), true);
            $provider = $meta['provider'] ?? null;

            // dependencies?
            if (!empty($meta['requires']) && is_array($meta['requires'])) {
                foreach ($meta['requires'] as $dep) {
                    $depPlugin = Plugin::where('slug', $dep)
                        ->where('enabled', true)
                        ->first();
                    if (!$depPlugin) {
                        Log::warning("[PluginLoader] Skipped '{$slug}': missing dependency '{$dep}'");
                        continue 2;
                    }
                }
            }

            // 1) Register the ServiceProvider
            if ($provider && class_exists($provider)) {
                app()->register($provider);
            }

            // 2) Run migrations from plugins/{slug}/migrations
            $migPath = "{$basePath}/migrations";
            if (File::isDirectory($migPath)) {
                Artisan::call('migrate', [
                    '--path' => "plugins/{$slug}/migrations",
                    '--force' => true,
                ]);
                Log::info("[PluginLoader] Ran migrations for '{$slug}'");
            }

            // 3) Publish assets if any
            $assets = "{$basePath}/resources/assets";
            $public = public_path("plugins/{$slug}");
            if (File::isDirectory($assets)) {
                File::ensureDirectoryExists($public);
                File::copyDirectory($assets, $public);
                Log::info("[PluginLoader] Published assets for '{$slug}'");
            }
        }
    }

    /**
     * Disable a plugin: rollback its migrations and mark it as disabled.
     */
    public function deactivate(string $slug): void
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) {
            return;
        }

        $migPath = base_path("plugins/{$slug}/migrations");
        if (File::isDirectory($migPath)) {
            Artisan::call('migrate:rollback', [
                '--path' => "plugins/{$slug}/migrations",
                '--force' => true,
            ]);
            Log::info("[PluginLoader] Rolled back migrations for '{$slug}'");
        }

        $plugin->enabled = false;
        $plugin->save();
    }

    /**
     * Remove a plugin entirely: deactivate, delete files, delete DB record.
     */
    public function remove(string $slug): void
    {
        $this->deactivate($slug);

        $path = base_path("plugins/{$slug}");
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        Plugin::where('slug', $slug)->delete();
        Log::info("[PluginLoader] Deleted plugin '{$slug}' and its DB entry");
    }
}