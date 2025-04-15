<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('slider_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained()->onDelete('cascade')->index();
            $table->string('image');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_images');
    }
};