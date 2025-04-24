<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            // new JSON column, nullable
            $table->json('options')->nullable()->after('custom_css');
        });
    }

    public function down()
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }

};