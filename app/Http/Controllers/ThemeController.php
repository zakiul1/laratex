<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

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
            if (!file_exists($publicShot) && file_exists($folderPath . '/screenshot.png')) {
                File::ensureDirectoryExists(dirname($publicShot));
                File::copy($folderPath . '/screenshot.png', $publicShot);
            }
    
            $themes[] = $data;
        }
    
        $activeTheme = $this->getActiveTheme();
    
        return view('themes.index', [
            'themes' => $themes,
            'activeTheme' => $activeTheme
        ]);
    }
    
    public function activate($folder)
    {
        if (Schema::hasTable('site_settings')) {
            $settings = SiteSetting::first();
    
            if (!$settings) {
                // ✅ Create default settings if missing
                $settings = new SiteSetting();
            }
    
            $settings->active_theme = $folder;
            $settings->save();
        } else {
            $envPath = base_path('.env');
            $env = File::get($envPath);
            $env = preg_replace('/^ACTIVE_THEME=.*/m', 'ACTIVE_THEME=' . $folder, $env);
            File::put($envPath, $env);
        }
    
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

        if (!is_dir($src)) {
            return back()->with('error', 'Source theme not found.');
        }

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
    
        // Prevent overwrite
        if (File::exists($extractPath)) {
            return back()->with('error', "Theme '{$themeName}' already exists.");
        }
    
        if ($zip->open($uploaded) === TRUE) {
            // Extract to temp location first
            $tempPath = storage_path("app/temp_themes/{$themeName}");
            File::ensureDirectoryExists($tempPath);
            $zip->extractTo($tempPath);
            $zip->close();
    
            // ✅ Validate structure
            if (!File::exists("{$tempPath}/theme.json")) {
                File::deleteDirectory($tempPath);
                return back()->with('error', 'theme.json is missing.');
            }
    
            if (!File::exists("{$tempPath}/views") && !count(File::files($tempPath))) {
                File::deleteDirectory($tempPath);
                return back()->with('error', 'Theme must contain views folder or blade files.');
            }
    
            // ✅ Move to theme directory
            File::moveDirectory($tempPath, $extractPath);
    
            return back()->with('success', "Theme '{$themeName}' installed successfully.");
        }
    
        return back()->with('error', 'Failed to open zip file.');
    }
    
    public function destroy($theme)
    {
        $activeTheme = $this->getActiveTheme();

        if ($theme === $activeTheme) {
            return back()->with('error', 'Cannot delete the active theme.');
        }

        $themePath = resource_path("views/themes/{$theme}");

        if (is_dir($themePath)) {
            File::deleteDirectory($themePath);
            return back()->with('success', "Theme '{$theme}' deleted successfully.");
        }

        return back()->with('error', 'Theme not found.');
    }

    public function edit($folder)
    {
        // For future theme editing options
        return back();
    }
    
    private function getActiveTheme()
    {
        if (Schema::hasTable('site_settings')) {
            $settings = SiteSetting::first();
    
            if (!$settings) {
                // Auto-create fallback record
                $settings = SiteSetting::create(['active_theme' => 'default']);
            }
    
            return $settings->active_theme;
        }
    
        return env('ACTIVE_THEME', 'default');
    }
    
}
