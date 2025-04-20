<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // idempotent: only create if missing
        if (! Schema::hasTable('ribbon_plugin_settings')) {
            Schema::create('ribbon_plugin_settings', function (Blueprint $table) {
                $table->id();
                $table->string('left_text')
                      ->default('SiATEX – Clothing manufacturer since 1987');
                $table->string('rfq_text')
                      ->default('RFQ Form');
                $table->string('rfq_url')->nullable();
                $table->string('phone')
                      ->default('(02) 222‑285‑548');
                $table->string('email')
                      ->default('sales@siatex.com');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // safe drop if exists
        if (Schema::hasTable('ribbon_plugin_settings')) {
            Schema::dropIfExists('ribbon_plugin_settings');
        }
    }
};
