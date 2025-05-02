<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_taxonomy_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_taxonomy_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();

            $table
                ->foreign('term_taxonomy_id')
                ->references('term_taxonomy_id')
                ->on('term_taxonomies')
                ->cascadeOnDelete();

            $table
                ->foreign('media_id')
                ->references('id')
                ->on('media')
                ->cascadeOnDelete();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('term_taxonomy_images');
    }
};