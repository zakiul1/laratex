<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->enum('layout', ['one-column', 'two-columns'])->default('one-column');
            $table->enum('image_position', ['left', 'right'])->default('left');
            $table->boolean('show_arrows')->default(true);
            $table->boolean('show_indicators')->default(true);
            $table->string('slider_location')->nullable(); // e.g. “homepage”, “header”
            $table->boolean('status')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sliders');
    }
}