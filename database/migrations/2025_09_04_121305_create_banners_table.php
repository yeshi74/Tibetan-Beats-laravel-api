<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_banners_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image_path');                           // storage path
            $table->enum('cta_type', ['none','song','playlist','album','url'])->default('none');
            $table->unsignedBigInteger('cta_id')->nullable();       // for song/playlist/album
            $table->string('cta_url')->nullable();                  // for url
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);        // for ordering
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('banners');
    }
};

