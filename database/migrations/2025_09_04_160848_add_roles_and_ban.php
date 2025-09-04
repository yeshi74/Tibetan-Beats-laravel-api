<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $t) {
                $t->id();
                $t->string('name')->unique(); // 'admin','moderator','artist','user'
                $t->timestamps();
            });

            DB::table('roles')->insert([
                ['name'=>'admin','created_at'=>now(),'updated_at'=>now()],
                ['name'=>'moderator','created_at'=>now(),'updated_at'=>now()],
                ['name'=>'artist','created_at'=>now(),'updated_at'=>now()],
                ['name'=>'user','created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','role_id')) {
                $t->foreignId('role_id')->nullable()->after('id')->constrained('roles');
            }
            if (!Schema::hasColumn('users','is_banned')) {
                $t->boolean('is_banned')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users','deleted_at')) {
                $t->softDeletes();
            }
        });

        // Default existing users to 'user'
        DB::statement("
            UPDATE users SET role_id = (SELECT id FROM roles WHERE name='user' LIMIT 1)
            WHERE role_id IS NULL
        ");

        // If you already have is_admin boolean, map those to role=admin (non-breaking)
        if (Schema::hasColumn('users','is_admin')) {
            DB::statement("
                UPDATE users
                SET role_id = (SELECT id FROM roles WHERE name='admin' LIMIT 1)
                WHERE is_admin = 1
            ");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users','role_id')) $t->dropConstrainedForeignId('role_id');
            if (Schema::hasColumn('users','is_banned')) $t->dropColumn('is_banned');
            if (Schema::hasColumn('users','deleted_at')) $t->dropSoftDeletes();
        });
        if (Schema::hasTable('roles')) Schema::drop('roles');
    }
};
