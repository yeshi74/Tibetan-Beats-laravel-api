<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dedications', function (Blueprint $t) {
            $t->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('dedications', function (Blueprint $t) {
            $t->dropConstrainedForeignId('to_user_id');
        });
    }
};
