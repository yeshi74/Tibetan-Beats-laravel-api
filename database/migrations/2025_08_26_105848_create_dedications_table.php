<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dedications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();
            $table->string('to_name', 120);
            $table->string('message', 280);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['song_id', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('dedications');
    }
};

