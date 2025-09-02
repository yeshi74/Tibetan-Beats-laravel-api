<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dedications', function (Blueprint $table) {
            if (!Schema::hasColumn('dedications', 'to_user_id')) {
                $table->foreignId('to_user_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('dedications', function (Blueprint $table) {
            if (Schema::hasColumn('dedications', 'to_user_id')) {
                $table->dropForeign(['to_user_id']);
                $table->dropColumn('to_user_id');
            }
        });
    }
};
