<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class PluginExportController extends Controller
{
    public function export($slug)
    {
        $pluginPath = base_path("plugins/{$slug}");

        if (!File::exists($pluginPath)) {
            abort(404, "Plugin not found.");
        }

        $zipFile = storage_path("app/tmp/{$slug}.zip");

        // Clean old zip if exists
        if (File::exists($zipFile)) {
            File::delete($zipFile);
        }

        File::ensureDirectoryExists(dirname($zipFile));

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            $this->zipFolder($pluginPath, $zip, strlen(dirname($pluginPath)) + 1);
            $zip->close();
        } else {
            abort(500, "Could not create zip.");
        }

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }

    protected function zipFolder($folder, ZipArchive $zip, $removeLength)
    {
        foreach (File::allFiles($folder) as $file) {
            $relativePath = substr($file->getPathname(), $removeLength);
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }
}