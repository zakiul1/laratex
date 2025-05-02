<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_taxonomies', function (Blueprint $table) {
            // Primary key
            $table->id('term_taxonomy_id');

            // Link back to the terms table
            $table->foreignId('term_id')
                ->constrained('terms', 'id')
                ->cascadeOnDelete();

            // Type of taxonomy (e.g. category, tag, product)
            $table->string('taxonomy');

            // Optional description
            $table->text('description')->nullable();

            // Parent term_taxonomy_id (0 = no parent)
            $table->unsignedBigInteger('parent')->default(0);

            // Usage count (for WP-like count tracking)
            $table->unsignedBigInteger('count')->default(0);

            // Active/inactive toggle
            $table->boolean('status')->default(1);

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_taxonomies');
    }
};