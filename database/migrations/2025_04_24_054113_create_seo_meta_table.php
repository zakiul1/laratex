<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoMetaTable extends Migration
{
    public function up()
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('metable');          // metable_id + metable_type
            $table->json('meta')->nullable();   // will hold title, robots, description, keywords
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_meta');
    }
}