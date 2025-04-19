<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('file_name')->unique();
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('media_folders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}