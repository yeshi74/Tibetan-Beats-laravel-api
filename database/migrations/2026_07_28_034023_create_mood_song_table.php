<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_song', function (Blueprint $table) {

            $table->id();

            $table->foreignId('mood_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('song_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'mood_id',
                'song_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_song');
    }
};
