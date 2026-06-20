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
        Schema::create('artist_applications', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('display_name');

        $table->string('real_name')->nullable();

        $table->string('country')->nullable();

        $table->string('profile_image')->nullable();

        $table->json('social_links')->nullable();

        $table->json('meta')->nullable();

        $table->string('status')
            ->default('pending');

        $table->text('admin_notes')
            ->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_applications');
    }
};
