<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PluginUpdateController extends Controller
{
    public function update($slug)
    {
        $jsonPath = base_path("plugins/{$slug}/plugin.json");

        if (!File::exists($jsonPath)) {
            return back()->with('error', 'Plugin not found.');
        }

        $meta = json_decode(File::get($jsonPath), true);
        $downloadUrl = $meta['update_url'] ?? null;

        if (!$downloadUrl) {
            return back()->with('error', 'No update URL found in plugin.json.');
        }

        try {
            $remoteData = Http::timeout(10)->get($downloadUrl)->json();
            $zipUrl = $remoteData['download_url'] ?? null;

            if (!$zipUrl) {
                return back()->with('error', 'Download URL missing from update endpoint.');
            }

            // Download zip
            $tmpPath = storage_path("app/tmp/plugins");
            File::ensureDirectoryExists($tmpPath);
            $zipFile = "{$tmpPath}/{$slug}.zip";

            file_put_contents($zipFile, file_get_contents($zipUrl));

            // Unzip and replace plugin folder
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === true) {
                File::deleteDirectory(base_path("plugins/{$slug}"));
                $zip->extractTo(base_path("plugins/{$slug}"));
                $zip->close();
                File::delete($zipFile);
            } else {
                return back()->with('error', 'Failed to extract ZIP.');
            }

            return back()->with('success', "Plugin '{$slug}' updated successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}