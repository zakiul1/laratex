<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSliderItemsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('slider_items')) {
            Schema::create('slider_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('slider_id')
                    ->constrained('sliders')
                    ->onDelete('cascade');
                $t->string('image_path');
                $t->json('content')->nullable(); // { title, subtitle, buttons: [...] }
                $t->integer('sort_order')->default(0);
                $t->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('slider_items');
    }
}