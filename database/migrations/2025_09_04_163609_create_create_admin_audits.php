<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('admin_id')->constrained('users');
            $t->string('event');
            $t->json('payload')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('admin_audits');
    }
};
