<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Plugin;
use App\Models\PluginSetting;

class PluginSettingController extends Controller
{
    public function edit($slug)
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();

        // Get saved settings
        $saved = PluginSetting::where('plugin_slug', $slug)->pluck('value', 'key')->toArray();

        // Load settings schema from plugin config
        $schemaPath = base_path("plugins/{$slug}/config/settings.php");
        $fields = File::exists($schemaPath) ? include $schemaPath : [];

        return view('admin.plugins.settings.edit', compact('plugin', 'saved', 'fields'));
    }

    public function update(Request $request, $slug)
    {
        $fields = include base_path("plugins/{$slug}/config/settings.php");

        foreach ($fields as $key => $field) {
            $value = match ($field['type']) {
                'checkbox' => $request->has($key),
                default => $request->input($key),
            };

            set_plugin_setting($slug, $key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated.');
    }
}