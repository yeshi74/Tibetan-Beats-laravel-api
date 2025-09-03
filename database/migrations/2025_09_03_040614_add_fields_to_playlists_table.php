<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            if (!Schema::hasColumn('playlists', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('playlists', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('cover_image');
            }
            if (!Schema::hasColumn('playlists', 'type')) {
                $table->string('type')->nullable()->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'is_featured', 'type']);
        });
    }
};

