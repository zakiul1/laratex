<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('theme_sections', function (Blueprint $table) {
            $table->id();
            $table->string('theme'); // e.g. 'classic'
            $table->string('key');   // e.g. 'hero', 'about', 'features'
            $table->string('title')->nullable(); // For admin label
            $table->integer('order')->default(0); // Drag order
            $table->json('settings')->nullable(); // Per-section config
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_sections');
    }
};
