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
        Schema::create('ribbons', function (Blueprint $table) {
            $table->id();
            $table->string('left_text')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('bg_color')->default('#0a4b78');
            $table->string('text_color')->default('#ffffff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ribbons');
    }
};