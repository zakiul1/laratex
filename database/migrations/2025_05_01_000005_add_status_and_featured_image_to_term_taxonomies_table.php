<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('term_taxonomies', function (Blueprint $table) {
            // add status & featured_image
            $table->tinyInteger('status')->default(1)->after('parent');
            $table->string('featured_image')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('term_taxonomies', function (Blueprint $table) {
            $table->dropColumn(['status', 'featured_image']);
        });
    }
};
