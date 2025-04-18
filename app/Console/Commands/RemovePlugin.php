<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Plugin;
use App\Contracts\PluginLifecycleInterface;

class RemovePlugin extends Command
{
    protected $signature = 'plugin:remove {slug}';
    protected $description = 'Uninstall and remove a plugin';

    public function handle()
    {
        $slug = $this->argument('slug');
        $pluginPath = base_path("plugins/{$slug}");
        $jsonPath = "{$pluginPath}/plugin.json";

        if (!File::exists($jsonPath)) {
            $this->error("Plugin '{$slug}' not found.");
            return;
        }

        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            $this->warn("Plugin '{$slug}' is not installed in the database.");
        }

        $meta = json_decode(File::get($jsonPath), true);
        $provider = $meta['provider'] ?? null;

        if ($provider && class_exists($provider)) {
            $instance = app($provider);

            if ($instance instanceof PluginLifecycleInterface) {
                $instance->deactivate(); // ✅ Call deactivate
                $instance->uninstall();  // ✅ Call uninstall
            }
        }

        // 🔥 Remove DB entry
        $plugin?->delete();

        // ❌ Optionally: delete the plugin folder
        // File::deleteDirectory($pluginPath);

        $this->info("Plugin '{$slug}' has been uninstalled and removed.");
    }
}