<?php

namespace App\Http\Controllers\Admin;
use App\Contracts\PluginLifecycleInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Plugin;

class PluginController extends Controller
{
    public function index()
    {
        $plugins = Plugin::all();
        $updates = app(\App\Services\PluginUpdateChecker::class)->fetchAvailableUpdates();

        return view('admin.plugins.index', compact('plugins', 'updates'));

    }




    public function toggle($id)
    {
        $plugin = Plugin::findOrFail($id);
        $wasEnabled = $plugin->enabled;
        $plugin->enabled = !$plugin->enabled;
        $plugin->save();

        // Check other plugins that require this one
        if (!$plugin->enabled) {
            // Auto-disable dependents
            $dependents = Plugin::where('enabled', true)->get()->filter(function ($p) use ($plugin) {
                $jsonPath = base_path("plugins/{$p->slug}/plugin.json");

                if (!File::exists($jsonPath))
                    return false;

                $meta = json_decode(File::get($jsonPath), true);

                return in_array($plugin->slug, $meta['requires'] ?? []);
            });

            foreach ($dependents as $dependent) {
                $dependent->update(['enabled' => false]);
            }
        }

        return redirect()->back()->with('success', 'Plugin status updated.');
    }


    public function destroy(Plugin $plugin)
    {
        $pluginPath = base_path("plugins/{$plugin->slug}");

        // Delete plugin folder
        if (File::exists($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        // Remove from DB
        $plugin->delete();

        return redirect()->route('admin.plugins.index')->with('success', 'Plugin deleted successfully.');
    }

}