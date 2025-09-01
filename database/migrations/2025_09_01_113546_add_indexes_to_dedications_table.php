<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dedications', function (Blueprint $table) {
            // Single-column indexes
            $table->index('created_at');
            $table->index('user_id');
            $table->index('to_user_id');
            $table->index('song_id');

            // Composite index for faster daily limit checks
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('dedications', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['to_user_id']);
            $table->dropIndex(['song_id']);
            $table->dropIndex(['user_id', 'created_at']); // drop composite index
        });
    }
};
