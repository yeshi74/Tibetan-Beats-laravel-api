<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'artists';
    protected $uniqueIndex = 'artists_user_id_unique';
    protected $foreignKey = 'artists_user_id_foreign';

    public function up()
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        // Add columns safely
        Schema::table($this->table, function (Blueprint $table) {
            if (! Schema::hasColumn($this->table, 'real_name')) {
                $table->string('real_name')->nullable()->after('display_name');
            }
            if (! Schema::hasColumn($this->table, 'country')) {
                $table->string('country')->nullable()->after('real_name');
            }
            if (! Schema::hasColumn($this->table, 'profile_image')) {
                $table->string('profile_image')->nullable()->after('stripe_connect_id');
            }
            if (! Schema::hasColumn($this->table, 'social_links')) {
                $table->json('social_links')->nullable()->after('meta');
            }

            if (! Schema::hasColumn($this->table, 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
        });

        // Add unique index if not present (information_schema.statistics)
        $indexExists = DB::selectOne(
            "SELECT COUNT(1) AS cnt
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?",
            [$this->table, $this->uniqueIndex]
        );

        if (! $indexExists || $indexExists->cnt == 0) {
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->unique('user_id', 'artists_user_id_unique');
                });
            } catch (\Throwable $e) {
                // ignore race/duplicate errors
            }
        }

        // Add foreign key if users table exists and FK not present
        $fkExists = DB::selectOne(
            "SELECT COUNT(1) AS cnt
             FROM information_schema.table_constraints
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND constraint_type = 'FOREIGN KEY'
               AND constraint_name = ?",
            [$this->table, $this->foreignKey]
        );

        if (! $fkExists || $fkExists->cnt == 0) {
            $usersExists = DB::selectOne(
                "SELECT COUNT(1) AS cnt FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND table_name = 'users'"
            );

            if ($usersExists && $usersExists->cnt > 0) {
                try {
                    Schema::table($this->table, function (Blueprint $table) {
                        $table->foreign('user_id', 'artists_user_id_foreign')
                              ->references('id')->on('users')
                              ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // ignore FK creation errors
                }
            }
        }
    }

    public function down()
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        // Drop foreign key if present
        $fkExists = DB::selectOne(
            "SELECT COUNT(1) AS cnt
             FROM information_schema.table_constraints
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND constraint_type = 'FOREIGN KEY'
               AND constraint_name = ?",
            [$this->table, $this->foreignKey]
        );

        if ($fkExists && $fkExists->cnt > 0) {
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->dropForeign('artists_user_id_foreign');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Drop unique index if present
        $indexExists = DB::selectOne(
            "SELECT COUNT(1) AS cnt
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?",
            [$this->table, $this->uniqueIndex]
        );

        if ($indexExists && $indexExists->cnt > 0) {
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->dropUnique('artists_user_id_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Drop columns we added (if they exist)
        Schema::table($this->table, function (Blueprint $table) {
            if (Schema::hasColumn($this->table, 'social_links')) {
                $table->dropColumn('social_links');
            }
            if (Schema::hasColumn($this->table, 'profile_image')) {
                $table->dropColumn('profile_image');
            }
            if (Schema::hasColumn($this->table, 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn($this->table, 'real_name')) {
                $table->dropColumn('real_name');
            }
        });
    }
};
