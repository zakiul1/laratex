<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->enum('layout', ['one-column', 'two-column'])->default('one-column');
            $table->enum('image_position', ['left', 'right'])->default('left');
            $table->boolean('show_arrows')->default(true);
            $table->boolean('show_indicators')->default(true);
            $table->string('slider_location')->default('home');
            $table->boolean('status')->default(true);           // ✅ added
            $table->integer('order')->default(0);               // ✅ added

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};