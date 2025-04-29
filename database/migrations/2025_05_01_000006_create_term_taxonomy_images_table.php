<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_taxonomy_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_taxonomy_id')
                ->constrained('term_taxonomies', 'term_taxonomy_id')
                ->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_taxonomy_images');
    }
};