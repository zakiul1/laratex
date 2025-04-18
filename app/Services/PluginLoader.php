<?php
// app/Services/PluginLoader.php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Plugin;

class PluginLoader
{
    public function sync(): void
    {
        $pluginFolders = File::directories(base_path('plugins'));

        foreach ($pluginFolders as $folder) {
            $slug = basename($folder);
            $jsonPath = "{$folder}/plugin.json";

            if (!File::exists($jsonPath)) {
                continue;
            }

            $meta = json_decode(File::get($jsonPath), true);
            $provider = $meta['provider'] ?? null;

            if (!$provider || !class_exists($provider)) {
                continue;
            }

            Plugin::updateOrCreate(['slug' => $slug], [
                'name' => $meta['name'] ?? $slug,
                'description' => $meta['description'] ?? '',
                'version' => $meta['version'] ?? '1.0.0',
            ]);
        }
    }

    public function bootEnabled(): void
    {
        $enabledPlugins = Plugin::where('enabled', true)->get();

        foreach ($enabledPlugins as $plugin) {
            $slug = $plugin->slug;
            $basePath = base_path("plugins/{$slug}");
            $jsonPath = "{$basePath}/plugin.json";

            if (!File::exists($jsonPath)) {
                continue;
            }

            $meta = json_decode(File::get($jsonPath), true);
            $provider = $meta['provider'] ?? null;

            if (!empty($meta['requires']) && is_array($meta['requires'])) {
                foreach ($meta['requires'] as $requiredSlug) {
                    $requiredPlugin = Plugin::where('slug', $requiredSlug)->where('enabled', true)->first();
                    if (!$requiredPlugin) {
                        Log::warning("[PluginLoader] Skipped {$slug} due to missing dependency: {$requiredSlug}");
                        continue 2;
                    }
                }
            }

            if (!empty($meta['version']) && version_compare($meta['version'], $plugin->version, '>')) {
                $plugin->update(['version' => $meta['version']]);
            }

            // ✅ Register the plugin
            if ($provider && class_exists($provider)) {
                app()->register($provider);
            }

            // ✅ Run plugin migrations (if available)
            $migrationPath = "{$basePath}/database/migrations";
            if (File::isDirectory($migrationPath)) {
                Artisan::call('migrate', [
                    '--path' => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
                Log::info("[PluginLoader] Ran migrations for: {$slug}");
            }

            // ✅ Publish plugin assets (if available)
            $assetsPath = "{$basePath}/resources/assets";
            $publicPath = public_path("plugins/{$slug}");

            if (File::isDirectory($assetsPath)) {
                File::ensureDirectoryExists($publicPath);
                File::copyDirectory($assetsPath, $publicPath);
                Log::info("[PluginLoader] Published assets for: {$slug}");
            }
        }
    }

    public function deactivate(string $slug): void
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin)
            return;

        // Rollback plugin migrations
        $migrationPath = base_path("plugins/{$slug}/database/migrations");
        if (File::isDirectory($migrationPath)) {
            Artisan::call('migrate:rollback', [
                '--path' => "plugins/{$slug}/database/migrations",
                '--force' => true,
            ]);
            Log::info("[PluginLoader] Rolled back migrations for: {$slug}");
        }

        $plugin->update(['enabled' => false]);
    }

    public function remove(string $slug): void
    {
        $this->deactivate($slug);

        // Delete plugin folder
        $path = base_path("plugins/{$slug}");
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        Plugin::where('slug', $slug)->delete();

        Log::info("[PluginLoader] Deleted plugin and DB entry: {$slug}");
    }
}