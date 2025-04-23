<?php
// database/migrations/xxxx_xx_xx_add_home_page_slug_to_site_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHomePageSlugToSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_page_slug')->nullable()->after('active_theme');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('home_page_slug');
        });
    }
}