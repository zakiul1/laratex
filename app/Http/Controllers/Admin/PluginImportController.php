<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class PluginImportController extends Controller
{
    public function showForm()
    {
        return view('admin.plugins.import');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'plugin_zip' => 'required|mimes:zip|max:20480', // 20MB max
        ]);

        $file = $request->file('plugin_zip');
        $tmpPath = storage_path('app/tmp_uploads');
        File::ensureDirectoryExists($tmpPath);

        $filename = $file->getClientOriginalName();
        $zipPath = "{$tmpPath}/{$filename}";
        $file->move($tmpPath, $filename);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Could not open the zip file.');
        }

        // Read plugin.json inside zip
        $pluginJson = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (Str::endsWith($entry, 'plugin.json')) {
                $pluginJson = json_decode($zip->getFromIndex($i), true);
                break;
            }
        }

        if (!$pluginJson || empty($pluginJson['slug'])) {
            return back()->with('error', 'Invalid plugin structure. Missing plugin.json or slug.');
        }

        $slug = $pluginJson['slug'];
        $targetPath = base_path("plugins/{$slug}");

        if (File::exists($targetPath)) {
            return back()->with('error', 'A plugin with this slug already exists.');
        }

        $zip->extractTo($targetPath);
        $zip->close();
        File::delete($zipPath);

        return redirect()->route('admin.plugins.index')->with('success', "Plugin '{$slug}' imported successfully.");
    }
}