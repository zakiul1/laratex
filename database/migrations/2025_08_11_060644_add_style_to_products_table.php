<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Style number to identify products
            $table->string('style', 64)->nullable()->unique()->after('slug');
            // If you prefer non-unique, change to: ->index() instead of ->unique()
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['style']); // remove if you used index instead
            $table->dropColumn('style');
        });
    }
};