<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaablesTable extends Migration
{
    public function up()
    {
        Schema::create('mediaables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->morphs('mediable');   // mediable_type, mediable_id
            $table->string('zone')->nullable(); // e.g. "featured", "gallery"
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediaables');
    }
}