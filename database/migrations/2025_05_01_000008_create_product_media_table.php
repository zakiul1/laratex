<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table) {
            // If you want composite primary key:
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('media_id');

            $table->primary(['product_id', 'media_id']);

            // optional foreign keys:
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->foreign('media_id')
                ->references('id')->on('media')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_media', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['media_id']);
        });

        Schema::dropIfExists('product_media');
    }
};