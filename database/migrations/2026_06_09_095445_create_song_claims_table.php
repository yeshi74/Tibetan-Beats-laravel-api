<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('song_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('song_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('song_title');

            $table->string('claimant_name');

            $table->string('email');

            $table->text('reason');

            $table->string('status')->default('pending');
            // pending, approved, rejected, resolved

            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_claims');
    }
};
