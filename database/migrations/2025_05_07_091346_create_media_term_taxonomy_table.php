<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_term_taxonomy', function (Blueprint $table) {
            // FK to the media table
            $table->unsignedBigInteger('media_id');
            // FK to the term_taxonomies table
            $table->unsignedBigInteger('term_taxonomy_id');
            // discriminator for polymorphic use (will be 'media')
            $table->string('object_type')->nullable();
            // timestamps if you want to track when links were created/updated
            $table->timestamps();

            // composite PK to prevent duplicates
            $table->primary(
                ['media_id', 'term_taxonomy_id', 'object_type'],
                'media_term_taxonomy_pk'
            );

            // foreign key constraints
            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->onDelete('cascade');

            $table->foreign('term_taxonomy_id')
                ->references('term_taxonomy_id')
                ->on('term_taxonomies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_term_taxonomy');
    }
};