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
        Schema::create('song_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('song_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reason');

            $table->text('details')->nullable();

            $table->enum('status', [
                'pending',
                'reviewed',
                'dismissed'
            ])->default('pending');

            $table->timestamps();
        });
    }


};
