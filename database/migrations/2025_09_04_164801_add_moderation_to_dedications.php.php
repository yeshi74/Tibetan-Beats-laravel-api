<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dedications', function (Blueprint $t) {
            if (!Schema::hasColumn('dedications','status')) {
                $t->string('status')->default('pending')->index(); // pending|approved|rejected
            }
            if (!Schema::hasColumn('dedications','reviewed_by')) {
                $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('dedications','reviewed_at')) {
                $t->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('dedications','reject_reason')) {
                $t->string('reject_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dedications', function (Blueprint $t) {
            if (Schema::hasColumn('dedications','reject_reason')) $t->dropColumn('reject_reason');
            if (Schema::hasColumn('dedications','reviewed_at')) $t->dropColumn('reviewed_at');
            if (Schema::hasColumn('dedications','reviewed_by')) $t->dropConstrainedForeignId('reviewed_by');
            if (Schema::hasColumn('dedications','status')) $t->dropColumn('status');
        });
    }
};
