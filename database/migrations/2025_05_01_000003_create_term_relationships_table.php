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
        Schema::create('term_relationships', function (Blueprint $table) {
            // primary key
            $table->id();

            // link to your term_taxonomies table
            $table->unsignedBigInteger('term_taxonomy_id');

            // polymorphic object ID (e.g. product, post, media)
            $table->unsignedBigInteger('object_id');

            // object type enum (product, post, media, etc.)
            $table->string('object_type')
                ->default('product')
                ->comment('e.g. "product", "post", "media"');

            // timestamps for pivot
            $table->timestamps();

            // foreign key constraint
            $table->foreign('term_taxonomy_id')
                ->references('term_taxonomy_id')
                ->on('term_taxonomies')
                ->onDelete('cascade');

            // prevent duplicate assignments of the same object to the same taxonomy
            $table->unique(
                ['term_taxonomy_id', 'object_id', 'object_type'],
                'term_rel_unique'
            );

            // index to speed up lookups by type + id
            $table->index(
                ['object_type', 'object_id'],
                'term_rel_obj_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('term_relationships');
    }
};