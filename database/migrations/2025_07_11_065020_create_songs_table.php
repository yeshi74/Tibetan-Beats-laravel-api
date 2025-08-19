<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('genre')->nullable();
        $table->string('artist')->nullable();
        $table->string('file_path');
        $table->string('cover_image')->nullable();
        $table->foreignId('album_id')->nullable()->constrained()->onDelete('set null');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
