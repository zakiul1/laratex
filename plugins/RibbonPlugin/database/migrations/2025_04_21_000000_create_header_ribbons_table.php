<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderRibbonsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('header_ribbons')) {
            Schema::create('header_ribbons', function (Blueprint $table) {
                $table->id();
                $table->string('left_text')->nullable();
                $table->string('center_text')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('bg_color')->default('#0A4979');
                $table->string('text_color')->default('#ffffff');
                $table->integer('height')->default(32);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('header_ribbons');
    }
}