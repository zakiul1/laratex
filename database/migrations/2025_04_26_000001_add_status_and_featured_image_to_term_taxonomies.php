<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('term_taxonomies', function (Blueprint $table) {
            // add a status flag (1 = active, 0 = inactive)
            $table->boolean('status')->default(1)->after('taxonomy');
            // add featured_image path
            $table->string('featured_image')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('term_taxonomies', function (Blueprint $table) {
            $table->dropColumn(['status', 'featured_image']);
        });
    }
};