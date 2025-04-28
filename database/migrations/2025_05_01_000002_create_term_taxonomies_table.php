<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_taxonomies', function (Blueprint $table) {
            $table->id('term_taxonomy_id'); // Change from $table->id() to explicitly name the column
            $table->foreignId('term_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('taxonomy');       // e.g. "category", "tag"
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent')->default(0);
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_taxonomies');
    }
};