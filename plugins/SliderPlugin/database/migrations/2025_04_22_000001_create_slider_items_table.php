<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSliderItemsTable extends Migration
{
    public function up()
    {
        // If you’re re-creating from scratch, drop any existing table first:
        Schema::dropIfExists('slider_items');

        Schema::create('slider_items', function (Blueprint $t) {
            $t->id();

            // Link back to sliders table
            $t->foreignId('slider_id')
                ->constrained('sliders')
                ->onDelete('cascade');

            // Legacy file-upload path (nullable because we'll now prefer media_id)
            $t->string('image_path')
                ->nullable()
                ->comment('Legacy fallback if no media_id selected');

            // New: reference to Laratex media library
            $t->unsignedBigInteger('media_id')
                ->nullable()
                ->comment('References id in media table');
            $t->foreign('media_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();

            // Slide content JSON
            $t->json('content')->nullable(); // { title, subtitle, buttons: [...] }

            // Order inside the slider
            $t->integer('sort_order')->default(0);

            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slider_items');
    }
}