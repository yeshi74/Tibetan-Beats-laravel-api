<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type')->nullable(); // free text, e.g. "broadcast","dedication","promo"
            $table->json('payload')->nullable(); // extra json to send to clients
            $table->string('audience')->default('all'); // 'all' | 'role:moderator' | 'users:[1,2,3]' | 'premium'
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('scheduled_at')->nullable(); // future scheduling
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
