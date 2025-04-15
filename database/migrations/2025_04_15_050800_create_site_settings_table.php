<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // General site config
            $table->string('site_name')->nullable();
            $table->string('logo')->nullable();

            // Ribbon control
            $table->boolean('show_ribbon')->default(true);
            $table->string('ribbon_left_text')->nullable();
            $table->string('ribbon_phone')->nullable();
            $table->string('ribbon_email')->nullable();
            $table->string('ribbon_bg_color')->default('#0a4b78');
            $table->string('ribbon_text_color')->default('#ffffff');

            // Extra settings
            $table->json('extra')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};