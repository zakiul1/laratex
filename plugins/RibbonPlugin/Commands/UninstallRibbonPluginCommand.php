<?php

namespace Plugins\RibbonPlugin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class UninstallRibbonPluginCommand extends Command
{
    protected $signature = 'ribbon-plugin:uninstall';
    protected $description = 'Drop the header_ribbons table and clean up plugin data';

    public function handle()
    {
        if (Schema::hasTable('header_ribbons')) {
            Schema::drop('header_ribbons');
            $this->info('Dropped header_ribbons table.');
        } else {
            $this->info('No header_ribbons table found.');
        }
    }
}