<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeController extends Controller
{
    public function index()
    {
        $path = resource_path('views/themes');
        $folders = File::directories($path);

        $themes = [];
        foreach ($folders as $folderPath) {
            $folder = basename($folderPath);
            $jsonPath = $folderPath . '/theme.json';
            $screenshot = 'themes/' . $folder . '/screenshot.png';

            $data = [
                'folder' => $folder,
                'name' => Str::headline($folder),
                'description' => 'No description provided.',
                'screenshot' => $screenshot,
            ];

            if (file_exists($jsonPath)) {
                $meta = json_decode(file_get_contents($jsonPath), true);
                $data['name'] = $meta['name'] ?? $data['name'];
                $data['description'] = $meta['description'] ?? $data['description'];
            }

            // Copy screenshot to public if not exists
            $publicShot = public_path($screenshot);
            if (!file_exists($publicShot)) {
                if (file_exists($folderPath . '/screenshot.png')) {
                    File::ensureDirectoryExists(dirname($publicShot));
                    File::copy($folderPath . '/screenshot.png', $publicShot);
                }
            }

            $themes[] = $data;
        }

        $activeTheme = config('view.active_theme');
        return view('themes.index', compact('themes', 'activeTheme'));
    }

    public function activate($folder)
    {
        $envPath = base_path('.env');
        $env = File::get($envPath);
        $env = preg_replace('/^ACTIVE_THEME=.*/m', 'ACTIVE_THEME=' . $folder, $env);
        File::put($envPath, $env);

        return back()->with('success', 'Theme activated: ' . $folder);
    }

    public function preview($folder)
    {
        $themePath = resource_path("views/themes/$folder");
        if (!File::exists($themePath))
            abort(404);

        $files = File::allFiles($themePath);
        return view('themes.preview', compact('folder', 'files'));
    }

    public function duplicate(Request $request, $folder)
    {
        $src = resource_path("views/themes/$folder");
        $newFolder = $folder . '-' . time();
        $dest = resource_path("views/themes/$newFolder");
        File::copyDirectory($src, $dest);

        return back()->with('success', 'Theme duplicated as: ' . $newFolder);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'theme' => 'required|file|mimes:zip',
        ]);

        $zip = new ZipArchive;
        $uploaded = $request->file('theme');
        $themeName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
        $extractPath = resource_path('views/themes/' . $themeName);

        if ($zip->open($uploaded) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            return back()->with('success', 'Theme installed successfully: ' . $themeName);
        } else {
            return back()->with('error', 'Failed to extract theme.');
        }
    }

    public function edit($folder)
    {
        // Future editable theme settings
        return back();
    }
}