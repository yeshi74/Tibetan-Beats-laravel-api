<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('in_app_message_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('in_app_message_id')->constrained('in_app_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_id')->nullable(); // for non-auth clients
            $table->integer('impressions')->default(0);
            $table->timestamp('last_shown_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['in_app_message_id','user_id']);
            $table->index(['in_app_message_id','device_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('in_app_message_views');
    }
};
