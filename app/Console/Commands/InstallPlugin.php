<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Plugin;
use App\Contracts\PluginLifecycleInterface;

class InstallPlugin extends Command
{
    protected $signature = 'plugin:install {slug}';
    protected $description = 'Install a plugin by slug (runs install method, migrations, assets, and enables it)';

    public function handle()
    {
        $slug = $this->argument('slug');

        $pluginPath = base_path("plugins/{$slug}");
        $jsonPath = "{$pluginPath}/plugin.json";

        if (!File::exists($jsonPath)) {
            $this->error("Plugin {$slug} not found.");
            return;
        }

        $meta = json_decode(File::get($jsonPath), true);
        $provider = $meta['provider'] ?? null;

        if (!$provider || !class_exists($provider)) {
            $this->error("Service provider class not found: {$provider}");
            return;
        }

        // Register plugin if not in DB
        $plugin = Plugin::firstOrCreate(['slug' => $slug], [
            'name' => $meta['name'] ?? $slug,
            'description' => $meta['description'] ?? '',
            'version' => $meta['version'] ?? '1.0.0',
            'enabled' => true
        ]);

        $instance = app($provider);

        // Run install()
        if ($instance instanceof PluginLifecycleInterface) {
            $instance->install();
            $instance->activate(); // optional: activate immediately
        }

        // Run migrations
        $migrationPath = "plugins/{$slug}/database/migrations";
        if (File::isDirectory(base_path($migrationPath))) {
            \Artisan::call('migrate', ['--path' => $migrationPath, '--force' => true]);
            $this->info("Migrations for {$slug} completed.");
        }

        // Publish assets
        \Artisan::call('vendor:publish', ['--tag' => "{$slug}-assets", '--force' => true]);

        $this->info("Plugin {$slug} installed and activated.");
    }
}