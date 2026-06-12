<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('artist_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('claimant_name');
            $table->string('email');

            $table->text('reason');

            $table->string('status')
                ->default('pending');

            $table->text('admin_notes')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_claims');
    }
};
