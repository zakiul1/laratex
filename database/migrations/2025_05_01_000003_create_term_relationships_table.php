<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('term_taxonomy_id');
            $table->unsignedBigInteger('object_id');    // e.g. post ID
            $table->string('object_type')->default('post');
            $table->primary(
                ['term_taxonomy_id', 'object_id', 'object_type'],
                'term_rel_pk'
            );
            $table->foreign('term_taxonomy_id')
                ->references('term_taxonomy_id')
                ->on('term_taxonomies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_relationships');
    }
};