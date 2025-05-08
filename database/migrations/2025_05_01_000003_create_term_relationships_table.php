<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_relationships', function (Blueprint $table) {
            // references a row in term_taxonomies
            $table->unsignedBigInteger('term_taxonomy_id');

            // polymorphic: will hold a Post ID or a Media ID
            $table->unsignedBigInteger('object_id');

            // must be 'post' or 'media' (or any other object type you add)
            $table->string('object_type')->nullable();

            // add these two lines:
            $table->timestamps();

            // composite primary key to prevent duplicates
            $table->primary(
                ['term_taxonomy_id', 'object_id', 'object_type'],
                'term_rel_pk'
            );

            // foreign key on the taxonomy side
            $table->foreign('term_taxonomy_id')
                ->references('term_taxonomy_id')
                ->on('term_taxonomies')
                ->onDelete('cascade');

            // no FK on object_id because it's polymorphic
            // add an index for faster lookups by type+id
            $table->index(
                ['object_type', 'object_id'],
                'term_rel_obj_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_relationships');
    }
};