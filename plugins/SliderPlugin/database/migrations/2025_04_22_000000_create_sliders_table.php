<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sliders')) {
            Schema::create('sliders', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();

                // added 'carousel' to the enum
                $t->enum('layout', ['pure', 'with-content', 'carousel'])
                    ->default('pure')
                    ->comment('pure|with-content|carousel');

                $t->string('location')
                    ->default('header')
                    ->comment('header|footer|sidebar');

                // NEW!
                $t->text('heading')->nullable()
                    ->comment('Shown only when layout = with-content');
                $t->text('slogan')->nullable()
                    ->comment('Shown only when layout = with-content');

                $t->boolean('show_indicators')->default(true);
                $t->boolean('show_arrows')->default(true);
                $t->boolean('autoplay')->default(false);
                $t->boolean('is_active')->default(true);

                $t->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sliders');
    }
}